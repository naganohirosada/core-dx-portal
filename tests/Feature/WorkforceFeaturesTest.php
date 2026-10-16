<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\PaidLeave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\Feature\Concerns\CreatesTenants;
use Tests\TestCase;

class WorkforceFeaturesTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    // ---------- 勤怠集計 ----------

    public function test_worked_hours_excludes_break(): void
    {
        $attendance = new Attendance([
            'clock_in' => '09:00:00', 'clock_out' => '18:00:00',
            'break_start' => '12:00:00', 'break_end' => '13:00:00',
        ]);
        $this->assertSame(8.0, $attendance->workedHours());

        $this->assertSame(0.0, (new Attendance(['clock_in' => '09:00:00']))->workedHours());
    }

    public function test_dashboard_aggregates_attendance_and_company_average(): void
    {
        Carbon::setTestNow('2026-10-16 19:00:00');
        $me = $this->createTenantUser('general');
        $colleague = $this->createTenantUser('general', $me->systemSetting);

        $this->actingAs($me);
        Attendance::create(['user_id' => $me->id, 'date' => '2026-10-15', 'clock_in' => '09:00:00', 'clock_out' => '18:00:00', 'break_start' => '12:00:00', 'break_end' => '13:00:00']);
        $this->actingAs($colleague);
        Attendance::create(['user_id' => $colleague->id, 'date' => '2026-10-16', 'clock_in' => '09:00:00', 'clock_out' => '13:00:00']);

        $this->actingAs($me)->withoutVite()
            ->get('/dashboard')
            ->assertInertia(fn ($page) => $page
                ->where('workTimeData.personal.2', 8)     // 第3週（15〜21日）
                ->where('workTimeData.team_avg.2', 6));  // (8 + 4) / 2名

        Carbon::setTestNow();
    }

    public function test_monthly_summary_for_managers(): void
    {
        $admin = $this->createTenantUser('admin');
        $this->actingAs($admin);
        Attendance::create(['user_id' => $admin->id, 'date' => '2026-10-01', 'clock_in' => '09:00:00', 'clock_out' => '17:30:00']);
        Attendance::create(['user_id' => $admin->id, 'date' => '2026-10-02', 'clock_in' => '09:00:00']);

        $this->withoutVite()
            ->get(route('attendance.summary', ['month' => '2026-10']))
            ->assertInertia(fn ($page) => $page
                ->component('Attendance/Summary')
                ->where('rows.0.work_days', 2)
                ->where('rows.0.total_hours', 8.5)
                ->where('rows.0.missing_clock_out', 1));

        $general = $this->createTenantUser('general', $admin->systemSetting);
        $this->actingAs($general)->get(route('attendance.summary'))->assertForbidden();
    }

    // ---------- 有給残日数 ----------

    public function test_paid_leave_balance_counts_weekdays(): void
    {
        $user = $this->createTenantUser('admin');
        $user->forceFill(['paid_leave_days' => 3])->save();
        $this->actingAs($user);

        // 2026-10-16(金)〜10-19(月) は平日2日
        $this->post('/paid-leaves', ['start_date' => '2026-10-16', 'end_date' => '2026-10-19'])->assertSessionHasNoErrors();
        $this->patch(route('paid-leaves.approve', PaidLeave::first()));

        $balance = $user->fresh()->paidLeaveBalance();
        $this->assertSame(2, $balance['used']);
        $this->assertSame(1, $balance['remaining']);

        // 残1日に対して2日の申請は不可
        $this->post('/paid-leaves', ['start_date' => '2026-10-21', 'end_date' => '2026-10-22'])->assertSessionHasErrors('start_date');
        $this->assertSame(1, PaidLeave::count());
    }

    // ---------- 社員管理・ID上限 ----------

    public function test_admin_can_add_user_within_limit(): void
    {
        $admin = $this->createTenantUser('admin', null, ['max_users' => 2]);
        $this->actingAs($admin);

        $this->post(route('users.store'), [
            'name' => '現場 次郎', 'email' => 'jiro@example.com', 'password' => 'password123',
            'role' => 'general', 'paid_leave_days' => 12,
        ])->assertSessionHasNoErrors();

        $jiro = User::where('email', 'jiro@example.com')->first();
        $this->assertSame($admin->system_setting_id, $jiro->system_setting_id);
        $this->assertSame(12, (int) $jiro->paid_leave_days);

        // 上限2名に達したので3人目は追加不可
        $this->post(route('users.store'), [
            'name' => '三郎', 'email' => 'saburo@example.com', 'password' => 'password123',
            'role' => 'general', 'paid_leave_days' => 10,
        ])->assertSessionHasErrors('email');
        $this->assertSame(2, User::count());
    }

    public function test_non_admin_cannot_manage_users_and_tenants_are_separated(): void
    {
        $admin = $this->createTenantUser('admin');
        $manager = $this->createTenantUser('manager', $admin->systemSetting);
        $this->actingAs($manager)->get(route('users.index'))->assertForbidden();

        $otherAdmin = $this->createTenantUser('admin');
        $this->actingAs($otherAdmin)
            ->patch(route('users.update', $manager->id), ['role' => 'admin', 'paid_leave_days' => 10])
            ->assertNotFound();
    }

    public function test_admin_cannot_demote_self(): void
    {
        $admin = $this->createTenantUser('admin');
        $this->actingAs($admin)
            ->patch(route('users.update', $admin->id), ['role' => 'general', 'paid_leave_days' => 10])
            ->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    // ---------- 顧客CSV取込 ----------

    public function test_company_csv_import_supports_shift_jis(): void
    {
        $this->actingAs($this->createTenantUser());

        $csv = "企業名,郵便番号,住所,電話番号,インボイス番号\n"
            ."山田建設株式会社,100-0001,東京都千代田区1-1,03-1234-5678,T1234567890123\n"
            .",100-0002,名前なし,,\n"
            ."鈴木工務店,,,,\n";
        $file = UploadedFile::fake()->createWithContent('companies.csv', mb_convert_encoding($csv, 'SJIS-win', 'UTF-8'));

        $this->post(route('companies.import'), ['file' => $file])
            ->assertRedirect(route('companies.index'))
            ->assertSessionHas('import_result', fn ($r) => $r['imported'] === 2 && count($r['errors']) === 1);

        $this->assertSame(['山田建設株式会社', '鈴木工務店'], Company::orderBy('id')->pluck('name')->all());
        $this->assertSame('T1234567890123', Company::first()->registration_number);
    }
}

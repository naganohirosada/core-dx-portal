<?php

namespace Tests\Feature;

use App\Http\Controllers\AccountingController;
use App\Models\Invoice;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTenants;
use Tests\TestCase;

class AccountingTenantIsolationTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_journals_from_invoices_are_isolated_per_tenant(): void
    {
        $userA = $this->createTenantUser();
        $projectA = $this->createProjectFor($userA);
        $this->post('/invoices', $this->invoicePayload($projectA, 100000))->assertRedirect();

        $userB = $this->createTenantUser();
        $projectB = $this->createProjectFor($userB);
        $this->post('/invoices', $this->invoicePayload($projectB, 300000))->assertRedirect();

        // 会社Bから見えるのは自社の仕訳（1件）だけ
        $this->actingAs($userB);
        $this->assertSame(1, Journal::count());
        $statements = app(AccountingController::class)->getFinancialStatements()->getData(true);
        $sales = collect($statements['trial_balance'])->firstWhere('code', '4111');
        $this->assertEquals(330000, $sales['credit_total']);

        // 会社Aの売上は会社Aの金額のみ
        $this->actingAs($userA);
        $statements = app(AccountingController::class)->getFinancialStatements()->getData(true);
        $sales = collect($statements['trial_balance'])->firstWhere('code', '4111');
        $this->assertEquals(110000, $sales['credit_total']);

        // 仕訳・明細には会社IDが付与されている
        $this->assertSame(0, Journal::withoutGlobalScopes()->whereNull('system_setting_id')->count());
        $this->assertSame(0, JournalDetail::withoutGlobalScopes()->whereNull('system_setting_id')->count());
    }

    public function test_invoice_numbers_can_repeat_across_tenants(): void
    {
        $userA = $this->createTenantUser();
        $this->post('/invoices', $this->invoicePayload($this->createProjectFor($userA)))->assertRedirect();

        $userB = $this->createTenantUser();
        $this->post('/invoices', $this->invoicePayload($this->createProjectFor($userB)))->assertRedirect();

        $numbers = Invoice::withoutGlobalScopes()->pluck('invoice_number')->all();
        $this->assertSame(['INV-20261001-001', 'INV-20261001-001'], $numbers);
    }

    public function test_invoice_issuer_is_own_company(): void
    {
        $this->createTenantUser('admin', null, ['company_name' => '別会社']); // 先に作られた別テナント
        $user = $this->createTenantUser('admin', null, ['company_name' => '自社株式会社']);
        $this->post('/invoices', $this->invoicePayload($this->createProjectFor($user)))->assertRedirect();

        $invoice = Invoice::first();
        $this->withoutVite()
            ->get(route('invoices.show', $invoice))
            ->assertInertia(fn ($page) => $page->where('issuer.company_name', '自社株式会社'));
    }

    public function test_registration_creates_company_and_admin(): void
    {
        $this->post('/register', [
            'company_name' => '新規建設株式会社',
            'name' => '登録 太郎',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::withoutGlobalScopes()->where('email', 'new@example.com')->first();
        $this->assertNotNull($user->system_setting_id);
        $this->assertSame('admin', $user->role);
        $this->assertSame('新規建設株式会社', SystemSetting::find($user->system_setting_id)->company_name);
    }
}

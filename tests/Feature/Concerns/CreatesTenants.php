<?php

namespace Tests\Feature\Concerns;

use App\Models\Company;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Models\User;

/**
 * テスト用に会社（テナント）・ユーザー・顧客・案件を用意するヘルパー
 */
trait CreatesTenants
{
    protected function createTenantUser(string $role = 'admin', ?SystemSetting $setting = null, array $settingAttributes = []): User
    {
        // max_users など運営側で設定する項目も指定できるよう forceCreate を使う
        $setting ??= SystemSetting::forceCreate(array_merge([
            'company_name' => 'テスト株式会社',
            'registration_number' => 'T1234567890123',
        ], $settingAttributes));

        $user = User::factory()->create();
        $user->forceFill(['system_setting_id' => $setting->id, 'role' => $role])->save();

        return $user->fresh();
    }

    /** ログイン中ユーザーの会社に、顧客と案件を1件ずつ作成する */
    protected function createProjectFor(User $user): Project
    {
        $this->actingAs($user);
        $company = Company::create(['name' => '顧客A株式会社']);

        return Project::create(['company_id' => $company->id, 'name' => '新築工事A']);
    }

    protected function invoicePayload(Project $project, int $unitPrice = 100000, array $overrides = []): array
    {
        return array_merge([
            'project_id' => $project->id,
            'transaction_date' => '2026-10-01',
            'issue_date' => '2026-10-01',
            'due_date' => '2026-10-31',
            'items' => [
                ['description' => '工事代金', 'quantity' => 1, 'unit_price' => $unitPrice, 'tax_rate' => 10],
            ],
        ], $overrides);
    }
}

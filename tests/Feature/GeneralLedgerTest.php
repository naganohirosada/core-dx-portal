<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTenants;
use Tests\TestCase;

class GeneralLedgerTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    public function test_ledger_shows_entries_and_running_balance(): void
    {
        $user = $this->createTenantUser();
        $project = $this->createProjectFor($user);
        $this->post('/invoices', $this->invoicePayload($project, 100000));
        $this->post('/invoices', $this->invoicePayload($project, 50000, ['issue_date' => '2026-10-02', 'due_date' => '2026-11-02']));

        $receivable = Account::where('code', '1131')->first();

        $this->withoutVite()
            ->get(route('accounting.general-ledger', ['account_id' => $receivable->id]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Accounting/GeneralLedger')
                ->where('selectedAccount.code', '1131')
                ->has('rows', 2)
                ->where('rows.0.debit', 110000)
                ->where('rows.1.balance', 165000));
    }

    public function test_ledger_does_not_show_other_tenant_account(): void
    {
        $userA = $this->createTenantUser();
        $this->post('/invoices', $this->invoicePayload($this->createProjectFor($userA)));
        $accountA = Account::where('code', '1131')->first();

        $userB = $this->createTenantUser();
        $this->actingAs($userB)->withoutVite()
            ->get(route('accounting.general-ledger', ['account_id' => $accountA->id]))
            ->assertInertia(fn ($page) => $page->where('selectedAccount', null)->has('rows', 0)->has('accounts', 0));
    }
}

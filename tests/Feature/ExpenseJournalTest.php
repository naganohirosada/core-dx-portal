<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Journal;
use App\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTenants;
use Tests\TestCase;

class ExpenseJournalTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    private function submitExpense(array $overrides = [])
    {
        return $this->post('/workflows', array_merge([
            'type' => 'expense',
            'expense_category' => 'travel',
            'title' => '現場への交通費',
            'amount' => 12500,
        ], $overrides));
    }

    public function test_approving_expense_creates_journal(): void
    {
        $admin = $this->createTenantUser('admin');
        $this->actingAs($admin);
        $this->submitExpense()->assertRedirect();

        $workflow = Workflow::first();
        $this->patch(route('workflows.approve', $workflow))->assertRedirect();

        $this->assertSame('approved', $workflow->fresh()->status);
        $journal = Journal::with('details.account')->first();
        $this->assertNotNull($journal);

        $debit = $journal->details->firstWhere('side', 'debit');
        $credit = $journal->details->firstWhere('side', 'credit');
        $this->assertSame('旅費交通費', $debit->account->name);
        $this->assertEquals(12500, $debit->amount);
        $this->assertSame('未払金', $credit->account->name);
        $this->assertEquals(12500, $credit->amount);
    }

    public function test_double_approval_does_not_duplicate_journal(): void
    {
        $this->actingAs($this->createTenantUser('admin'));
        $this->submitExpense();
        $workflow = Workflow::first();

        $this->patch(route('workflows.approve', $workflow));
        $this->patch(route('workflows.approve', $workflow))->assertSessionHasErrors('workflow');

        $this->assertSame(1, Journal::count());
    }

    public function test_ringi_approval_creates_no_journal(): void
    {
        $this->actingAs($this->createTenantUser('admin'));
        $this->post('/workflows', ['type' => 'ringi', 'title' => 'PC購入', 'amount' => 200000]);

        $this->patch(route('workflows.approve', Workflow::first()));

        $this->assertSame(0, Journal::count());
    }

    public function test_expense_requires_category_and_amount(): void
    {
        $this->actingAs($this->createTenantUser('general'));
        $this->submitExpense(['expense_category' => null, 'amount' => null])
            ->assertSessionHasErrors(['expense_category', 'amount']);
    }

    public function test_general_user_cannot_approve(): void
    {
        $setting = $this->createTenantUser('admin')->systemSetting;
        $general = $this->createTenantUser('general', $setting);
        $this->actingAs($general);
        $this->submitExpense();

        $this->patch(route('workflows.approve', Workflow::first()))->assertForbidden();
        $this->assertSame(0, Account::count());
    }

    public function test_other_tenant_cannot_approve(): void
    {
        $this->actingAs($this->createTenantUser('admin'));
        $this->submitExpense();
        $workflow = Workflow::first();

        $this->actingAs($this->createTenantUser('admin'));
        $this->patch(route('workflows.approve', $workflow->id))->assertNotFound();
    }
}

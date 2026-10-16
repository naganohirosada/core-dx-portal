<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Feature\Concerns\CreatesTenants;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    private function issueInvoice(int $unitPrice = 100000, array $overrides = []): Invoice
    {
        $user = $this->createTenantUser();
        $this->post('/invoices', $this->invoicePayload($this->createProjectFor($user), $unitPrice, $overrides));

        return Invoice::latest('id')->first(); // 税込 110,000円
    }

    private function balanceOf(string $code): float
    {
        $account = Account::where('code', $code)->first();
        $debit = JournalDetail::where('account_id', $account->id)->where('side', 'debit')->sum('amount');
        $credit = JournalDetail::where('account_id', $account->id)->where('side', 'credit')->sum('amount');

        return $debit - $credit;
    }

    public function test_partial_then_full_payment(): void
    {
        $invoice = $this->issueInvoice();

        $this->post(route('invoices.payments.store', $invoice), ['payment_date' => '2026-10-20', 'amount' => 60000])->assertRedirect();
        $this->assertSame('sent', $invoice->fresh()->status);
        $this->assertSame(50000, $invoice->fresh()->outstandingAmount());
        $this->assertEquals(50000, $this->balanceOf('1131')); // 売掛金残
        $this->assertEquals(60000, $this->balanceOf('1112')); // 普通預金

        $this->post(route('invoices.payments.store', $invoice), ['payment_date' => '2026-10-25', 'amount' => 50000])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertEquals(0, $this->balanceOf('1131'));
        $this->assertEquals(110000, $this->balanceOf('1112'));
    }

    public function test_payment_cannot_exceed_outstanding(): void
    {
        $invoice = $this->issueInvoice();

        $this->post(route('invoices.payments.store', $invoice), ['payment_date' => '2026-10-20', 'amount' => 110001])
            ->assertSessionHasErrors('amount');
        $this->assertSame(0, InvoicePayment::count());
    }

    public function test_status_change_to_paid_clears_only_remaining_balance(): void
    {
        $invoice = $this->issueInvoice();
        $this->post(route('invoices.payments.store', $invoice), ['payment_date' => '2026-10-20', 'amount' => 10000]);

        $this->patch(route('invoices.status.update', $invoice), ['status' => 'paid'])->assertRedirect();

        $this->assertSame(0, $invoice->fresh()->outstandingAmount());
        $this->assertEquals(0, $this->balanceOf('1131'));
        $this->assertEquals(110000, $this->balanceOf('1112'));
    }

    public function test_aging_buckets_by_days_past_due(): void
    {
        Carbon::setTestNow('2026-12-15');
        $user = $this->createTenantUser();
        $project = $this->createProjectFor($user);
        // 期日 10/31 → 45日超過（31〜60日）
        $this->post('/invoices', $this->invoicePayload($project, 100000));
        // 期日 12/31 → 期日内
        $this->post('/invoices', $this->invoicePayload($project, 200000, ['issue_date' => '2026-12-01', 'due_date' => '2026-12-31']));

        $this->withoutVite()
            ->get(route('accounting.receivables'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Accounting/Receivables')
                ->has('rows', 2)
                ->where('totals.d31_60', 110000)
                ->where('totals.current', 220000)
                ->where('totals.total', 330000)
                ->where('summary.0.company_name', '顧客A株式会社'));

        Carbon::setTestNow();
    }

    public function test_other_tenant_cannot_register_payment(): void
    {
        $invoice = $this->issueInvoice();

        $this->actingAs($this->createTenantUser());
        $this->post(route('invoices.payments.store', $invoice->id), ['payment_date' => '2026-10-20', 'amount' => 1000])
            ->assertNotFound();
    }
}

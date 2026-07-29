<?php 

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
	// 1 仕訳登録機能
	public function storeJournal(Request $request)
	{
		$validated = $request->validate([
			'date' => 'required|date',
			'description' => 'required|string',
			'details' => 'required|array|min:2',
			'details.*.account_id' => 'required|exists:accounts,id',
			'details.*.side' => 'required|in:debit,credit',
			'details.*.amount' => 'required|numeric|min:0',
		]);
		// 貸借一致のバリデーション
		$debitSum = collect($validated['details'])->where('side','debit')->sum('amount');

		$creditSum = collect($validated['details'])->where('side','credit')->sum('amount');

		if ($debitSum !== $creditSum) {
			return response()->json(['error' => '借方と貸方の合計金額が一致しません。'], 422);
		}
		DB::transaction(function () use ($validated) {
			$journal = Journal::create([
				'date' => $validated['date'],
				'description' => $validated['description'],
			]);
			foreach ($validated['details'] as $detail) {
				$journal->details()->create($detail);
			}
		});
		return response()->json(['message' => '仕訳を登録しました。']);
	}
	// 2 各種出納帳(例:現金の出納一覧)
	public function getCashBook(Request $request)
	{
		$cashAccount = Account::where('code', '1111')->firstOrFail();

		// 現金コード

		$details = JournalDetail::with('journal')
		->where('account_id', $cashAccount->id)
		->get()
		->map(function ($detail) {
			return [
				'date' => $detail->journal->date,
				'description' => $detail->journal->description,
				'inflow' => $detail->side === 'debit' ? $detail->amount : 0, // 資産の増加(借方)
				'outflow' => $detail->side === 'credit' ? $detail->amount : 0, // 資産の減少(貸方)
			];
		});
		return response()->json(['account' => $cashAccount->name,'ledger' => $details]);
	}
	// 3 総勘定元帳(勘定科目ごとの明細)
	public function getGeneralLedger($accountId)
	{
		$account = Account::findOrFail($accountId);

		$details = JournalDetail::with('journal')
		->where('account_id', $accountId)
		->get();
		return response()->json(['account' => $account, 'details' =>$details]);
	}
	// 4 合計残高試算表 (Trial Balance) & 財務三表(B/S, P/L)の集計
	public function getFinancialStatements()
	{
		$accounts = Account::all();
		$trialBalance = [];
		$balanceSheet = ['assets' => [], 'liabilities' => [], 'equity'=> []];

		$profitStatement = ['revenue' => [], 'expense' => []];
		foreach ($accounts as $account) {
			// 借方・貸方の合計を取得
			$debitSum = JournalDetail::where('account_id',
			$account->id)->where('side', 'debit')->sum('amount');
			$creditSum = JournalDetail::where('account_id',
			$account->id)->where('side', 'credit')->sum('amount');

			// 残高の計算(科目の性質による)
			if (in_array($account->type, ['asset', 'expense'])) {
				$balance = $debitSum - $creditSum;
			} else {
				$balance = $creditSum - $debitSum;
			}
			$trialBalance[] = [
				'code' => $account->code,
				'name' => $account->name,
				'debit_total' => $debitSum,
				'credit_total' => $creditSum,
				'balance' => $balance,
			];
			// 財務諸表(B/S or P/L)への振り分け
			if (in_array($account->type, ['asset', 'liability','equity'])) {

				$balanceSheet[$account->type . 's'][] = ['name' =>$account->name, 'amount' => $balance];

			} else {
				$profitStatement[$account->type][] = ['name' =>$account->name, 'amount' => $balance];
			}
		}
		return response()->json([
			'trial_balance' => $trialBalance,
			'balance_sheet' => $balanceSheet,
			'profit_and_loss' => $profitStatement
		]);
	}
}
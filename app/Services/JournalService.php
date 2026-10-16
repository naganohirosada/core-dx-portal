<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Journal;
use InvalidArgumentException;

/**
 * 仕訳の自動起票を行う。勘定科目はログイン中の会社（テナント）の科目を使い、なければ作成する。
 */
class JournalService
{
    /**
     * @param  string  $date  取引日（Y-m-d）
     * @param  string  $description  摘要
     * @param  array<int, array{code: string, name: string, type: string, side: string, amount: int|float}>  $lines
     */
    public function post(string $date, string $description, array $lines): Journal
    {
        $debit = collect($lines)->where('side', 'debit')->sum('amount');
        $credit = collect($lines)->where('side', 'credit')->sum('amount');
        if ($debit != $credit) {
            throw new InvalidArgumentException('借方と貸方の合計金額が一致しません。');
        }

        $journal = Journal::create(['date' => $date, 'description' => $description]);

        foreach ($lines as $line) {
            $account = Account::firstOrCreate(
                ['code' => $line['code']],
                ['name' => $line['name'], 'type' => $line['type']]
            );
            $journal->details()->create([
                'account_id' => $account->id,
                'side' => $line['side'],
                'amount' => $line['amount'],
            ]);
        }

        return $journal;
    }
}

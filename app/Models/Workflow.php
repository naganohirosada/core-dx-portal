<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class Workflow extends Model
{
    use HasFactory, BelongsToTenant;

    /** 経費区分 => [科目コード, 勘定科目名]（経費精算の承認時に借方科目として使う） */
    public const EXPENSE_ACCOUNTS = [
        'travel' => ['7111', '旅費交通費'],
        'supplies' => ['7112', '消耗品費'],
        'communication' => ['7113', '通信費'],
        'meeting' => ['7114', '会議費'],
        'misc' => ['7199', '雑費'],
    ];

    protected $fillable = [
        'user_id',
        'type',
        'expense_category',
        'title',
        'amount',
        'description',
        'status',
    ];

    /** 申請者へのリレーション */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
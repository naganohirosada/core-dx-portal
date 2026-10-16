<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class PaidLeave extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'reason',
        'status',
    ];

    // 💡 日付キャスト設定
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /** 申請したユーザーへのリレーション */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** 期間内の平日（土日を除く）の日数 */
    public static function countWeekdays(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $days = 0;
        foreach (\Carbon\CarbonPeriod::create($start, $end) as $date) {
            if (! $date->isWeekend()) {
                $days++;
            }
        }
        return $days;
    }
}
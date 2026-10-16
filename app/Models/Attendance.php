<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Attendance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'status',
    ];

    /** 実労働時間（時間単位・小数1桁）＝ 退勤 − 出勤 − 休憩。退勤前は0 */
    public function workedHours(): float
    {
        if (! $this->clock_in || ! $this->clock_out) {
            return 0.0;
        }

        $minutes = $this->minutesBetween($this->clock_in, $this->clock_out);
        if ($this->break_start && $this->break_end) {
            $minutes -= $this->minutesBetween($this->break_start, $this->break_end);
        }

        return round(max($minutes, 0) / 60, 1);
    }

    private function minutesBetween(string $from, string $to): int
    {
        return (int) ((strtotime($to) - strtotime($from)) / 60);
    }
}
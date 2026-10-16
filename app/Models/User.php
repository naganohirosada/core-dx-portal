<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\BelongsToTenant;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, BelongsToTenant;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function paidLeaves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaidLeave::class);
    }

    /** 承認済み・申請中の有給取得日数と残日数（土日を除く日数で数える） */
    public function paidLeaveBalance(): array
    {
        $leaves = $this->paidLeaves()->whereIn('status', ['approved', 'pending'])->get();
        $used = $leaves->where('status', 'approved')->sum(fn ($l) => PaidLeave::countWeekdays($l->start_date, $l->end_date));
        $pending = $leaves->where('status', 'pending')->sum(fn ($l) => PaidLeave::countWeekdays($l->start_date, $l->end_date));
        $granted = (int) ($this->paid_leave_days ?? 0);

        return [
            'granted' => $granted,
            'used' => $used,
            'pending' => $pending,
            'remaining' => $granted - $used,
        ];
    }

    public function workflows(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(Workflow::class);
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Event::class);
    }
}

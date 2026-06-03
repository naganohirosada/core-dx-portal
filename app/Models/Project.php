<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'start_date',
        'end_date',
        'budget',
        'status',
    ];

    /**
     * 💡 従属：この案件が所属している顧客を取得する
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * 💡 1対多：この案件に紐づくすべてのタスクを取得する（★これを追記！）
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
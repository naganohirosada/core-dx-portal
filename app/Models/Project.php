<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
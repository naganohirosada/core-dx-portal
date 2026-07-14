<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class Company extends Model
{
    use HasFactory, BelongsToTenant;

    // 一括画面からの登録を許可するカラムを指定
    protected $fillable = [
        'name',
        'postal_code',
        'address',
        'tel',
        'status',
        'registration_number'
    ];

    /**
     * 💡 1対多：顧客に紐づくすべての案件を取得する
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
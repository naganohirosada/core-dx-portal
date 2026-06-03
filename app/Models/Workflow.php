<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
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
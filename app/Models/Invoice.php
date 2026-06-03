<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    /** 紐づく案件へのリレーション */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
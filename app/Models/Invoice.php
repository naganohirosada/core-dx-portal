<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class Invoice extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'project_id',
        'company_id',
        'transaction_date',
        'invoice_number',
        'issue_date',
        'due_date',
        'amount',
        'status',
        'notes',
        'subtotal_10_percent',
        'tax_amount_10_percent',
        'subtotal_8_percent',
        'tax_amount_8_percent',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'transaction_date' => 'date',
    ];

    /** 紐づく案件へのリレーション */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /** 請求書の明細行 */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    /** 未回収残高（請求額 − 入金済額） */
    public function outstandingAmount(): int
    {
        return (int) $this->amount - (int) $this->payments()->sum('amount');
    }
}
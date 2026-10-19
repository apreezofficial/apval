<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loan_reference',
        'amount',
        'tenure_months',
        'interest_rate',
        'total_repayment',
        'amount_repaid',
        'due_date',
        'status',
        'reason_for_loan',
        'personal_info',
        'employment_info',
        'emergency_contacts',
        'id_card_url',
        'selfie_url',
        'rejection_reason',
        'approved_at',
        'disbursed_at',
        'repaid_at',
    ];

    protected $casts = [
        'amount'             => 'float',
        'interest_rate'      => 'float',
        'total_repayment'    => 'float',
        'amount_repaid'      => 'float',
        'tenure_months'      => 'integer',
        'personal_info'      => 'array',
        'employment_info'    => 'array',
        'emergency_contacts' => 'array',
        'due_date'           => 'datetime',
        'approved_at'        => 'datetime',
        'disbursed_at'       => 'datetime',
        'repaid_at'          => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0.0, (float)$this->total_repayment - (float)$this->amount_repaid);
    }
}

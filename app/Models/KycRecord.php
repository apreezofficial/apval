<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class KycRecord extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'level',
        'dob',
        'bvn',
        'bvn_verified',
        'status',
        'provider_response',
        'verified_at',
        // Tier 2
        'id_card_url',
        'utility_bill_url',
        // Tier 3
        'business_name',
        'cac_number',
        'cac_document_url',
        // Admin review
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $hidden = [
        'bvn',
        'nin',
        'dob',
        'provider_response',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'bvn_verified'      => 'boolean',
        'verified_at'       => 'datetime',
        'reviewed_at'       => 'datetime',
        'dob'               => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

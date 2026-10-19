<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'store_name',
        'description',
        'phone',
        'email',
        'store_address',
        'return_policy',
        'delivery_information',
        'store_logo_url',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

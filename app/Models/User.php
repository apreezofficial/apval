<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\ResetPasswordToFrontend;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Agent\AgentProfile;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;


    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = 'AGP-' . strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'avatar_url', 'address', 'password', 'status', 'role', 'level',
        'bvn', 'bvn_verified', 'dob', 'market', 'transaction_pin', 'transaction_pin_set_at',
        'transaction_pin_attempts', 'transaction_pin_locked_until', 'biometric_enabled',
        'two_factor_enabled', 'two_factor_secret', 'two_factor_enabled_at', 'two_factor_channel',
        'referral_code', 'referred_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'transaction_pin',
        'bvn',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'created_at',
        'updated_at',
    ];
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
            'transaction_pin_set_at' => 'datetime',
            'transaction_pin_locked_until' => 'datetime',
            'biometric_enabled' => 'boolean',
        ];
    }

    public function kycRecord()
    {
        return $this->hasOne(KycRecord::class);
    }

    public function vendorProfile()
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function wallets()
    {
        return $this->hasMany(\App\Models\Wallet::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'initiated_by');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(UserDeviceToken::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $base = rtrim(config('app.frontend_reset_url'), '/');
        $url = $base . "?token={$token}&email=" . urlencode($this->email);

        $this->notify(new ResetPasswordToFrontend($url));
    }

    public function agentProfile(): HasOne
    {
        return $this->hasOne(AgentProfile::class, 'user_id');
    }

}

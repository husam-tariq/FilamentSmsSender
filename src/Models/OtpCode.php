<?php

namespace HusamTariq\FilamentSmsSender\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OtpCode extends Model
{
    protected $fillable = [
        'recipient',
        'code',
        'identifier',
        'expires_at',
        'is_used',
        'used_at',
        'sms_provider_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('filamentsmssender.tables.otp_codes', 'sms_sender_otp_codes'));
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    public function scopeValid($query)
    {
        return $query->where('is_used', false)
            ->where('expires_at', '>', now());
    }

    public function scopeForRecipient($query, string $recipient, ?string $identifier = null)
    {
        $query = $query->where('recipient', $recipient);

        if ($identifier) {
            $query->where('identifier', $identifier);
        }

        return $query;
    }

    /**
     * Get the SMS provider that was used to send this OTP
     */
    public function smsProvider(): BelongsTo
    {
        return $this->belongsTo(SmsProvider::class, 'sms_provider_id');
    }
}

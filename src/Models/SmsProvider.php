<?php

namespace HusamTariq\FilamentSmsSender\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;

class SmsProvider extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'is_default',
        'request_method',
        'api_endpoint_url',
        'request_parameters',
        'headers',
        'otp_length',
        'otp_expiry_minutes',
        'otp_template',
        'is_active',
        'success_code',
        'success_body',
        'success_conditional_body',
    ];

    public $translatable = ['otp_template'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'request_parameters' => 'array',
        'headers' => 'array',
        'otp_length' => 'integer',
        'otp_expiry_minutes' => 'integer',
        'success_code' => 'integer',
    ];

    protected static function booted()
    {
        // Ensure only one default provider exists
        static::creating(function (SmsProvider $provider) {
            if ($provider->is_default) {
                static::where('is_default', true)->update(['is_default' => false]);
            }
        });

        static::updating(function (SmsProvider $provider) {
            if ($provider->is_default && $provider->isDirty('is_default')) {
                static::where('id', '!=', $provider->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get OTP codes associated with this provider
     */
    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class, 'sms_provider_id');
    }

    /**
     * Scope to get the default provider
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get active providers
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default SMS provider
     */
    public static function getDefault(): ?self
    {
        return static::default()->active()->first();
    }

    /**
     * Get provider by name
     */
    public static function getByName(string $name): ?self
    {
        return static::where('name', $name)->active()->first();
    }

    /**
     * Make this provider the default
     */
    public function makeDefault(): bool
    {
        static::where('is_default', true)->update(['is_default' => false]);

        return $this->update(['is_default' => true]);
    }

    /**
     * Get gateway configuration in the format expected by SmsService
     */
    public function getGatewayConfig(): array
    {
        return [
            'method' => $this->request_method,
            'endpoint' => $this->api_endpoint_url,
            'parameters' => $this->request_parameters ?? [],
            'headers' => $this->headers ?? [],
        ];
    }

    /**
     * Get OTP configuration in the format expected by OtpManager
     */
    public function getOtpConfig(): array
    {
        return [
            'length' => $this->otp_length,
            'expiry_minutes' => $this->otp_expiry_minutes,
            'template' => $this->otp_template,
        ];
    }

    /**
     * Check if this provider can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete if it's the only active provider
        $activeCount = static::active()->count();

        if ($activeCount <= 1 && $this->is_active) {
            return false;
        }

        // Cannot delete if it has pending OTP codes
        $pendingOtps = $this->otpCodes()
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->count();

        return $pendingOtps === 0;
    }

    /**
     * Get validation rules for request parameters
     */
    public static function getParameterValidationRules(): array
    {
        return [
            'request_parameters.*.key' => 'required|string|max:255',
            'request_parameters.*.value' => 'required|string|max:1000',
        ];
    }

    /**
     * Get validation rules for headers
     */
    public static function getHeaderValidationRules(): array
    {
        return [
            'headers.*.key' => 'required|string|max:255',
            'headers.*.value' => 'required|string|max:1000',
        ];
    }
}

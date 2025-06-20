<?php

namespace HusamTariq\FilamentSmsSender\Services;

use HusamTariq\FilamentSmsSender\Models\OtpCode;
use HusamTariq\FilamentSmsSender\Models\SmsProvider;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OtpManager
{
    /**
     * Generate a new OTP code using default provider settings
     */
    public function generate(string $recipient, ?string $identifier = null): string
    {
        $provider = SmsProvider::getDefault();

        if (!$provider) {
            // No provider available
            throw new \RuntimeException('No SMS provider available');
        }

        return $this->generateWithProvider($recipient, $identifier, $provider);
    }

    /**
     * Generate a new OTP code using specific provider settings
     */
    public function generateWithProvider(string $recipient, ?string $identifier, SmsProvider $provider): string
    {
        // Clean up expired OTPs
        $this->cleanupExpired();

        // Invalidate any existing valid OTPs for this recipient
        OtpCode::forRecipient($recipient, $identifier)
            ->valid()
            ->update(['is_used' => true, 'used_at' => now()]);

        // Get OTP configuration from provider
        $otpConfig = $provider->getOtpConfig();
        $length = $otpConfig['length'] ?? 6;
        $expiryMinutes = $otpConfig['expiry_minutes'] ?? 10;

        // Generate OTP
        $otpCode = $this->generateRandomCode($length);

        // Store OTP in database with provider reference
        OtpCode::create([
            'recipient' => $recipient,
            'code' => $otpCode,
            'identifier' => $identifier,
            'expires_at' => now()->addMinutes($expiryMinutes),
            'sms_provider_id' => $provider->id,
        ]);

        return $otpCode;
    }

    /**
     * Verify an OTP code
     */
    public function verify(string $recipient, string $otpCode, ?string $identifier = null): bool
    {
        $otp = OtpCode::forRecipient($recipient, $identifier)
            ->where('code', $otpCode)
            ->valid()
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->markAsUsed();
        return true;
    }

    /**
     * Check if OTP can be sent (rate limiting)
     */
    public function canSend(string $recipient): bool
    {
        $otpConfig = config('filamentsmssender.otp', []);
        $rateLimitConfig = $otpConfig['rate_limit'] ?? [];

        if (!($rateLimitConfig['enabled'] ?? true)) {
            return true;
        }

        $maxAttempts = $rateLimitConfig['max_attempts'] ?? 3;
        $cacheKey = "sms_otp_rate_limit:{$recipient}";
        $attempts = Cache::get($cacheKey, 0);

        return $attempts < $maxAttempts;
    }

    /**
     * Increment rate limit counter
     */
    public function incrementRateLimit(string $recipient): void
    {
        $rateLimitConfig = config('filamentsmssender.otp.rate_limit', []);
        $windowMinutes = $rateLimitConfig['window_minutes'] ?? 5;

        $cacheKey = "sms_otp_rate_limit:{$recipient}";
        $attempts = Cache::get($cacheKey, 0);

        Cache::put($cacheKey, $attempts + 1, now()->addMinutes($windowMinutes));
    }

    /**
     * Format OTP message using default provider template
     */
    public function formatMessage(string $otpCode): string
    {
        $provider = SmsProvider::getDefault();

        if (!$provider) {
            // No provider available
            throw new \RuntimeException('No SMS provider available');
        }

        return $this->formatMessageWithProvider($otpCode, $provider);
    }

    /**
     * Format OTP message using specific provider template
     */
    public function formatMessageWithProvider(string $otpCode, SmsProvider $provider): string
    {
        $otpConfig = $provider->getOtpConfig();
        $template = $otpConfig['template'] ?? 'Your OTP code is: {{ otp_code }}';

        return str_replace('{{ otp_code }}', $otpCode, $template);
    }

    /**
     * Check if OTP can be sent using provider-specific settings (rate limiting)
     */
    public function canSendWithProvider(string $recipient, SmsProvider $provider): bool
    {
        // For now, use global rate limiting. Could be extended per-provider
        return $this->canSend($recipient);
    }

    /**
     * Generate a random OTP code
     */
    protected function generateRandomCode(int $length): string
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;

        return (string) random_int($min, $max);
    }

    /**
     * Clean up expired OTP codes
     */
    protected function cleanupExpired(): void
    {
        OtpCode::where('expires_at', '<', now())
            ->where('is_used', false)
            ->update(['is_used' => true]);
    }

    /**
     * Get remaining rate limit attempts
     */
    public function getRemainingAttempts(string $recipient): int
    {
        $otpConfig = config('filamentssmssender.otp', []);
        $rateLimitConfig = $otpConfig['rate_limit'] ?? [];

        if (!($rateLimitConfig['enabled'] ?? true)) {
            return PHP_INT_MAX;
        }

        $maxAttempts = $rateLimitConfig['max_attempts'] ?? 3;
        $cacheKey = "sms_otp_rate_limit:{$recipient}";
        $attempts = Cache::get($cacheKey, 0);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get rate limit reset time
     */
    public function getRateLimitResetTime(string $recipient): ?Carbon
    {
        $cacheKey = "sms_otp_rate_limit:{$recipient}";

        if (!Cache::has($cacheKey)) {
            return null;
        }

        $otpConfig = config('filamentssmssender.otp', []);
        $rateLimitConfig = $otpConfig['rate_limit'] ?? [];
        $windowMinutes = $rateLimitConfig['window_minutes'] ?? 5;

        // Get the cache expiry time (this is an approximation)
        return now()->addMinutes($windowMinutes);
    }
}

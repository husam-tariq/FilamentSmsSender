<?php

namespace HusamTariq\FilamentSmsSender\Facades;

use Illuminate\Support\Facades\Facade;
use HusamTariq\FilamentSmsSender\Services\SmsService;

/**
 * @method static bool send(string $recipient, string $message)
 * @method static string|false sendOtp(string $recipient, ?string $identifier = null)
 * @method static bool verifyOtp(string $recipient, string $otpCode, ?string $identifier = null)
 *
 * @see \HusamTariq\FilamentSmsSender\Services\SmsService
 */
class FilamentSmsSender extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SmsService::class;
    }
}

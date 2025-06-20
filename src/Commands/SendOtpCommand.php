<?php

namespace HusamTariq\FilamentSmsSender\Commands;

use HusamTariq\FilamentSmsSender\Services\SmsService;
use Illuminate\Console\Command;

class SendOtpCommand extends Command
{
    public $signature = 'sms:send-otp
                        {recipient : Phone number of the recipient}
                        {--identifier= : Optional identifier for the OTP}';

    public $description = 'Send an OTP code to a phone number';

    public function handle(): int
    {
        $recipient = $this->argument('recipient');
        $identifier = $this->option('identifier');

        $this->info("Sending OTP to: {$recipient}");
        if ($identifier) {
            $this->info("Identifier: {$identifier}");
        }

        $smsService = app(SmsService::class);
        $otpCode = $smsService->sendOtp($recipient, $identifier);

        if ($otpCode) {
            $this->info("✅ OTP sent successfully!");
            $this->info("🔑 OTP Code: {$otpCode}");
            return self::SUCCESS;
        } else {
            $this->error('❌ Failed to send OTP. Check your configuration and logs.');
            return self::FAILURE;
        }
    }
}

class VerifyOtpCommand extends Command
{
    public $signature = 'sms:verify-otp
                        {recipient : Phone number of the recipient}
                        {code : The OTP code to verify}
                        {--identifier= : Optional identifier for the OTP}';

    public $description = 'Verify an OTP code';

    public function handle(): int
    {
        $recipient = $this->argument('recipient');
        $code = $this->argument('code');
        $identifier = $this->option('identifier');

        $this->info("Verifying OTP for: {$recipient}");
        $this->info("Code: {$code}");
        if ($identifier) {
            $this->info("Identifier: {$identifier}");
        }

        $smsService = app(SmsService::class);
        $isValid = $smsService->verifyOtp($recipient, $code, $identifier);

        if ($isValid) {
            $this->info('✅ OTP verified successfully!');
            return self::SUCCESS;
        } else {
            $this->error('❌ OTP verification failed. The code may be invalid, expired, or already used.');
            return self::FAILURE;
        }
    }
}

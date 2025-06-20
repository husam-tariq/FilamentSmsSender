<?php

namespace HusamTariq\FilamentSmsSender\Commands;

use HusamTariq\FilamentSmsSender\Services\SmsService;
use Illuminate\Console\Command;

class FilamentSmsSenderCommand extends Command
{
    public $signature = 'sms:send
                        {recipient : Phone number of the recipient}
                        {message : Message to send}
                        {--test : Run in test mode}';

    public $description = 'Send an SMS message using the configured gateway';

    public function handle(): int
    {
        $recipient = $this->argument('recipient');
        $message = $this->argument('message');
        $isTest = $this->option('test');

        if ($isTest) {
            $this->info('Running in test mode...');
        }

        $this->info("Sending SMS to: {$recipient}");
        $this->info("Message: {$message}");

        $smsService = app(SmsService::class);
        $success = $smsService->send($recipient, $message);

        if ($success) {
            $this->info('✅ SMS sent successfully!');
            return self::SUCCESS;
        } else {
            $this->error('❌ Failed to send SMS. Check your configuration and logs.');
            return self::FAILURE;
        }
    }
}

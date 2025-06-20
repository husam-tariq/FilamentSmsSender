<?php

namespace HusamTariq\FilamentSmsSender\Livewire;

use HusamTariq\FilamentSmsSender\Services\SmsService;
use HusamTariq\FilamentSmsSender\Services\OtpManager;
use Livewire\Component;

class OtpVerification extends Component
{
    public string $recipient = '';
    public string $otpCode = '';
    public string $identifier = '';
    public bool $otpSent = false;
    public bool $isVerified = false;
    public string $message = '';
    public string $messageType = 'info'; // success, error, info, warning

    protected $rules = [
        'recipient' => 'required|string',
        'otpCode' => 'required|string|min:4|max:8',
        'identifier' => 'nullable|string',
    ];

    public function sendOtp()
    {
        $this->validate(['recipient' => 'required|string']);

        $smsService = app(SmsService::class);
        $otpManager = app(OtpManager::class);

        // Check rate limiting
        if (!$otpManager->canSend($this->recipient)) {
            $this->setMessage(__('filamentsmssender::filamentsmssender.too_many_otp_requests'), 'error');
            return;
        }

        $otpCode = $smsService->sendOtp($this->recipient, $this->identifier ?: null);

        if ($otpCode) {
            $this->otpSent = true;
            $this->setMessage(__('filamentsmssender::filamentsmssender.otp_sent_successfully'), 'success');
        } else {
            $this->setMessage(__('filamentsmssender::filamentsmssender.failed_to_send_otp'), 'error');
        }
    }

    public function verifyOtp()
    {
        $this->validate();

        $smsService = app(SmsService::class);
        $isValid = $smsService->verifyOtp($this->recipient, $this->otpCode, $this->identifier ?: null);

        if ($isValid) {
            $this->isVerified = true;
            $this->setMessage(__('filamentsmssender::filamentsmssender.otp_verified_successfully'), 'success');
            $this->dispatch('otp-verified', [
                'recipient' => $this->recipient,
                'identifier' => $this->identifier,
            ]);
        } else {
            $this->setMessage(__('filamentsmssender::filamentsmssender.invalid_or_expired_otp'), 'error');
        }
    }

    public function resetForm()
    {
        $this->recipient = '';
        $this->otpCode = '';
        $this->identifier = '';
        $this->otpSent = false;
        $this->isVerified = false;
        $this->message = '';
        $this->messageType = 'info';
    }

    protected function setMessage(string $message, string $type = 'info')
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function render()
    {
        return view('filamentsmssender::livewire.otp-verification');
    }
}

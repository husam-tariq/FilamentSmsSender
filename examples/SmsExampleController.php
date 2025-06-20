<?php

namespace App\Http\Controllers\Examples;

use HusamTariq\FilamentSmsSender\Services\SmsService;
use HusamTariq\FilamentSmsSender\Facades\FilamentSmsSender;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Example controller showing how to integrate SMS and OTP functionality
 * into your Laravel application
 */
class SmsExampleController
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send a basic SMS message
     */
    public function sendSms(Request $request): JsonResponse
    {
        $request->validate([
            'recipient' => 'required|string',
            'message' => 'required|string',
        ]);

        // Method 1: Using the service directly
        $success = $this->smsService->send(
            $request->recipient,
            $request->message
        );

        // Method 2: Using the facade
        // $success = FilamentSmsSender::send($request->recipient, $request->message);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'SMS sent successfully' : 'Failed to send SMS'
        ]);
    }

    /**
     * Send an OTP for user registration
     */
    public function sendRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->phone;
        $identifier = 'user_registration';

        // Send OTP
        $otpCode = $this->smsService->sendOtp($phone, $identifier);

        if ($otpCode) {
            // Store phone in session for verification step
            session(['registration_phone' => $phone]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                // Don't return the actual OTP code in production!
                // 'otp_code' => $otpCode, // Only for testing
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send OTP'
        ], 400);
    }

    /**
     * Verify OTP for user registration
     */
    public function verifyRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp_code' => 'required|string',
        ]);

        $phone = session('registration_phone');
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'No pending registration found'
            ], 400);
        }

        $isValid = $this->smsService->verifyOtp(
            $phone,
            $request->otp_code,
            'user_registration'
        );

        if ($isValid) {
            // OTP verified successfully
            // You can now complete the user registration
            session()->forget('registration_phone');

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'phone_verified' => true
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ], 400);
    }

    /**
     * Send OTP for password reset
     */
    public function sendPasswordResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->phone;

        // In a real application, you would verify that this phone number
        // belongs to an existing user account

        $otpCode = $this->smsService->sendOtp($phone, 'password_reset');

        if ($otpCode) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset OTP sent successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send password reset OTP'
        ], 400);
    }

    /**
     * Verify OTP for password reset
     */
    public function verifyPasswordResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'otp_code' => 'required|string',
        ]);

        $isValid = $this->smsService->verifyOtp(
            $request->phone,
            $request->otp_code,
            'password_reset'
        );

        if ($isValid) {
            // Generate a temporary token for password reset
            $resetToken = bin2hex(random_bytes(32));

            // Store the token in cache or database
            cache()->put("password_reset_token:{$resetToken}", $request->phone, now()->addMinutes(15));

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'reset_token' => $resetToken
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ], 400);
    }

    /**
     * Send a notification SMS
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $request->validate([
            'recipient' => 'required|string',
            'type' => 'required|string|in:order_confirmation,payment_received,shipment_update',
            'data' => 'required|array',
        ]);

        $message = $this->buildNotificationMessage($request->type, $request->data);

        $success = $this->smsService->send($request->recipient, $message);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification sent successfully' : 'Failed to send notification'
        ]);
    }

    /**
     * Build notification message based on type and data
     */
    private function buildNotificationMessage(string $type, array $data): string
    {
        return match ($type) {
            'order_confirmation' => "Order #{$data['order_id']} confirmed! Total: {$data['total']}. Expected delivery: {$data['delivery_date']}",
            'payment_received' => "Payment of {$data['amount']} received for order #{$data['order_id']}. Thank you!",
            'shipment_update' => "Your order #{$data['order_id']} has been shipped! Tracking: {$data['tracking_number']}",
            default => "Notification: " . json_encode($data),
        };
    }
}

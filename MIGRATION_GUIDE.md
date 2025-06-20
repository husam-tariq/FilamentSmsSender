# Multi-Provider SMS System Migration Guide

This guide will help you migrate from the single SMS gateway configuration to the new multi-provider system.

## Migration Steps

### 1. Run the New Migration

```bash
php artisan vendor:publish --tag="filamentsmssender-migrations"
php artisan migrate
```

This will create the new `sms_providers` table and add the `sms_provider_id` column to the existing `sms_sender_otp_codes` table.

### 2. Migrate Existing Configuration (Optional)

If you have existing SMS gateway configuration in the old settings system, you can create a migration command to transfer it to the new provider system:

```php
// Create a migration command
php artisan make:command MigrateSmsSettings

// In the command:
use HusamTariq\FilamentSmsSender\Models\SmsSenderSetting;
use HusamTariq\FilamentSmsSender\Models\SmsProvider;

public function handle()
{
    $gatewayConfig = SmsSenderSetting::get('gateway_config');
    $otpConfig = SmsSenderSetting::get('otp_config');

    if ($gatewayConfig && !empty($gatewayConfig['endpoint'])) {
        SmsProvider::create([
            'name' => 'Legacy Provider',
            'is_default' => true,
            'request_method' => $gatewayConfig['method'] ?? 'POST',
            'api_endpoint_url' => $gatewayConfig['endpoint'],
            'request_parameters' => $gatewayConfig['parameters'] ?? [],
            'headers' => $gatewayConfig['headers'] ?? [],
            'otp_length' => $otpConfig['length'] ?? 6,
            'otp_expiry_minutes' => $otpConfig['expiry_minutes'] ?? 10,
            'otp_template' => $otpConfig['template'] ?? 'Your OTP code is: {{ otp_code }}',
        ]);

        $this->info('Legacy configuration migrated to new provider system.');
    }
}
```

### 3. Update Your Application Code

#### Before (Single Provider):
```php
use HusamTariq\FilamentSmsSender\Services\SmsService;

$smsService = app(SmsService::class);
$success = $smsService->send('+1234567890', 'Hello World!');
$otpCode = $smsService->sendOtp('+1234567890', 'registration');
```

#### After (Multi-Provider):
```php
use HusamTariq\FilamentSmsSender\Services\SmsService;

$smsService = app(SmsService::class);

// Using default provider (same as before)
$success = $smsService->send('+1234567890', 'Hello World!');
$otpCode = $smsService->sendOtp('+1234567890', 'registration');

// Using specific provider
$success = $smsService->send('+1234567890', 'Hello World!', 'Twilio');
$otpCode = $smsService->sendOtp('+1234567890', 'registration', 'AWS-SNS');
```

## New Features

### 1. SMS Provider Resource

Navigate to **SMS Management > SMS Providers** in your Filament admin panel to:

- Add multiple SMS providers
- Configure each provider's API settings
- Set a default provider
- Test providers individually
- Manage provider status (active/inactive)

### 2. Provider-Specific OTP Settings

Each provider can have its own OTP configuration:
- Custom OTP length (4-8 digits)
- Custom expiry time
- Custom message template

### 3. Provider Management Actions

- **Make Default**: Set any active provider as the default
- **Test**: Send test SMS using a specific provider
- **Activate/Deactivate**: Control which providers are available

### 4. Advanced Usage Examples

#### User Registration with Specific Provider
```php
class RegistrationController extends Controller
{
    public function sendVerificationSms(Request $request)
    {
        $smsService = app(SmsService::class);

        // Use a specific high-reliability provider for registration
        $otpCode = $smsService->sendOtp(
            $request->phone,
            'registration',
            'Twilio-Premium' // specific provider
        );

        if ($otpCode) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
```

#### Fallback Provider Logic
```php
class NotificationService
{
    public function sendUrgentAlert(string $phone, string $message): bool
    {
        $smsService = app(SmsService::class);

        // Try primary provider first
        if ($smsService->send($phone, $message, 'Primary-Provider')) {
            return true;
        }

        // Fallback to secondary provider
        if ($smsService->send($phone, $message, 'Backup-Provider')) {
            return true;
        }

        // Use default provider as last resort
        return $smsService->send($phone, $message);
    }
}
```

#### Provider-Specific OTP Verification
```php
class OtpController extends Controller
{
    public function verifyOtp(Request $request)
    {
        $smsService = app(SmsService::class);

        // Verification works with any provider
        $isValid = $smsService->verifyOtp(
            $request->phone,
            $request->otp_code,
            $request->identifier
        );

        if ($isValid) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
```

## Database Schema Changes

### New Tables

#### `sms_providers`
```sql
- id (bigint, primary key)
- name (string, unique)
- is_default (boolean)
- request_method (string)
- api_endpoint_url (string)
- request_parameters (json)
- headers (json)
- otp_length (integer)
- otp_expiry_minutes (integer)
- otp_template (string)
- is_active (boolean)
- created_at, updated_at (timestamps)
```

#### Updated `sms_sender_otp_codes`
```sql
- sms_provider_id (bigint, nullable, foreign key)
```

## Configuration Management

### Provider Configuration Examples

#### Twilio Provider
```
Name: Twilio
Method: POST
Endpoint: https://api.twilio.com/2010-04-01/Accounts/YOUR_SID/Messages.json

Parameters:
- From: +1234567890
- To: {{ recipient }}
- Body: {{ message }}

Headers:
- Authorization: Basic [base64_encoded_credentials]
```

#### AWS SNS Provider
```
Name: AWS SNS
Method: POST
Endpoint: https://sns.us-east-1.amazonaws.com/

Parameters:
- Action: Publish
- PhoneNumber: {{ recipient }}
- Message: {{ message }}

Headers:
- Authorization: AWS4-HMAC-SHA256 Credential=...
- Content-Type: application/x-www-form-urlencoded
```

## Best Practices

### 1. Provider Naming
Use descriptive names that indicate:
- Service provider (Twilio, AWS, etc.)
- Service tier (Premium, Standard, etc.)
- Geographic region if applicable

### 2. Default Provider Selection
Choose your most reliable provider as default:
- Best delivery rates
- Most stable API
- Best cost-effectiveness

### 3. Backup Strategies
- Keep at least 2 active providers
- Test providers regularly
- Monitor delivery rates

### 4. OTP Template Consistency
Maintain consistent OTP message formats across providers for better user experience.

## Troubleshooting

### Migration Issues

**Issue**: Migration fails with foreign key constraint error
**Solution**: Ensure the `sms_providers` table exists before adding the foreign key to `otp_codes`

**Issue**: Existing OTP codes reference null provider
**Solution**: This is expected and acceptable. New OTPs will reference the provider used.

### Provider Configuration Issues

**Issue**: Test SMS fails but configuration looks correct
**Solution**:
1. Check provider API documentation for exact parameter names
2. Verify authentication headers format
3. Check Filament logs for detailed error messages

**Issue**: No default provider available
**Solution**:
1. Ensure at least one provider is marked as default
2. Check that the default provider is active
3. Use the "Make Default" action in the provider list

## Backwards Compatibility

The system maintains backwards compatibility:
- Existing code using `send()` and `sendOtp()` without provider names will use the default provider
- Legacy settings are still supported if no providers are configured
- Existing OTP codes without provider references continue to work

## Performance Considerations

- Provider queries are optimized with database indexes
- Default provider is cached for performance
- Rate limiting remains global but can be extended per-provider in future versions

This migration provides enhanced flexibility while maintaining full backwards compatibility with your existing SMS and OTP functionality.

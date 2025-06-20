# FilamentPHP SMS Sender Plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/husam-tariq/filamentsmssender.svg?style=flat-square)](https://packagist.org/packages/husam-tariq/filamentsmssender)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/husam-tariq/filamentsmssender/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/husam-tariq/filamentsmssender/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/husam-tariq/filamentsmssender/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/husam-tariq/filamentsmssender/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/husam-tariq/filamentsmssender.svg?style=flat-square)](https://packagist.org/packages/husam-tariq/filamentsmssender)

A powerful FilamentPHP v3 plugin for sending SMS messages and implementing One-Time Password (OTP) verification in your Laravel applications. This plugin provides a flexible custom HTTP request builder that allows seamless integration with virtually any SMS gateway API without writing custom code for each provider.

## Features

🚀 **Custom HTTP Request Builder** - Configure any SMS API without code
📱 **OTP Verification System** - Complete OTP generation, sending, and verification
⚙️ **Filament Admin Interface** - Manage SMS providers and test gateways in real-time
🔒 **Rate Limiting** - Built-in protection against OTP abuse
🎯 **Multiple Identifiers** - Support for different OTP contexts (registration, password reset, etc.)
📊 **Comprehensive Logging** - Detailed logs for debugging and monitoring
🧪 **Testing Tools** - Artisan commands and admin panel testing features
🎨 **Livewire Components** - Ready-to-use OTP verification components

## Installation

Install the package via composer:

```bash
composer require husam-tariq/filamentsmssender
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filamentsmssender-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="filamentsmssender-config"
```

## Plugin Registration

Register the plugin in your Filament panel provider:

```php
use HusamTariq\FilamentSmsSender\FilamentSmsSenderPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentSmsSenderPlugin::make(),
        ]);
}
```

## Configuration

### Admin Panel Configuration

1. Navigate to **SMS Providers** in your Filament admin panel
2. Add and configure your SMS providers:
   - **Gateway Settings**: Set API endpoint, method, parameters, and headers
   - **OTP Settings**: Configure OTP length, expiry, and rate limiting

### Example Configuration

Here's an example configuration for a typical SMS API:

**Request Parameters:**
```
Key: api_key, Value: your-api-key-here
Key: to, Value: {{ recipient }}
Key: message, Value: {{ message }}
Key: from, Value: YourAppName
```

**Request Headers:**
```
Key: Authorization, Value: Bearer your-token-here
Key: Content-Type, Value: application/x-www-form-urlencoded
```

## Usage

### Basic SMS Sending

```php
use HusamTariq\FilamentSmsSender\Facades\FilamentSmsSender;
use HusamTariq\FilamentSmsSender\Services\SmsService;

// Using the facade
$success = FilamentSmsSender::send('+1234567890', 'Hello, World!');

// Using the service directly
$smsService = app(SmsService::class);
$success = $smsService->send('+1234567890', 'Hello, World!');
```

### OTP Functionality

#### Sending an OTP

```php
use HusamTariq\FilamentSmsSender\Services\SmsService;

$smsService = app(SmsService::class);

// Send OTP for user registration
$otpCode = $smsService->sendOtp('+1234567890', 'user_registration');

if ($otpCode) {
    // OTP sent successfully
    // Store the phone number in session for verification
    session(['pending_verification_phone' => '+1234567890']);
} else {
    // Failed to send OTP (rate limited or configuration error)
}
```

#### Verifying an OTP

```php
$isValid = $smsService->verifyOtp(
    '+1234567890',          // recipient
    '123456',               // OTP code
    'user_registration'     // identifier (optional)
);

if ($isValid) {
    // OTP is valid and has been marked as used
    // Complete the user registration process
} else {
    // OTP is invalid, expired, or already used
}
```

### Using the Livewire Component

Include the OTP verification component in your Blade views:

```blade
@livewire('otp-verification')
```

You can listen for the `otp-verified` event:

```javascript
document.addEventListener('livewire:load', function () {
    Livewire.on('otp-verified', (data) => {
        console.log('OTP verified for:', data.recipient);
        // Handle successful verification
    });
});
```

## Artisan Commands

The plugin provides several Artisan commands for testing and management:

### Send SMS

```bash
# Send a test SMS
php artisan sms:send "+1234567890" "Test message"

# Send in test mode
php artisan sms:send "+1234567890" "Test message" --test
```

### Send OTP

```bash
# Send an OTP
php artisan sms:send-otp "+1234567890"

# Send OTP with identifier
php artisan sms:send-otp "+1234567890" --identifier="test"
```

### Verify OTP

```bash
# Verify an OTP
php artisan sms:verify-otp "+1234567890" "123456"

# Verify OTP with identifier
php artisan sms:verify-otp "+1234567890" "123456" --identifier="test"
```

## SMS Gateway Integration Examples

### Twilio

```
Method: POST
Endpoint: https://api.twilio.com/2010-04-01/Accounts/YOUR_ACCOUNT_SID/Messages.json

Parameters:
- From: +1234567890
- To: {{ recipient }}
- Body: {{ message }}

Headers:
- Authorization: Basic [base64_encoded_credentials]
```

### Nexmo/Vonage

```
Method: POST
Endpoint: https://rest.nexmo.com/sms/json

Parameters:
- api_key: your_api_key
- api_secret: your_api_secret
- from: YourApp
- to: {{ recipient }}
- text: {{ message }}
```

### AWS SNS

```
Method: POST
Endpoint: https://sns.region.amazonaws.com/

Parameters:
- Action: Publish
- PhoneNumber: {{ recipient }}
- Message: {{ message }}

Headers:
- Authorization: AWS4-HMAC-SHA256 Credential=...
- Content-Type: application/x-www-form-urlencoded
```

## Installation

You can install the package via composer:

```bash
composer require husam-tariq/filamentsmssender
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filamentsmssender-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filamentsmssender-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filamentsmssender-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentSmsSender = new HusamTariq\FilamentSmsSender();
echo $filamentSmsSender->echoPhrase('Hello, HusamTariq!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Hussam Tariq](https://github.com/husam-tariq)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

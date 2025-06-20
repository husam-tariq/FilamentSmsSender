<?php

namespace HusamTariq\FilamentSmsSender\Services;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HusamTariq\FilamentSmsSender\Models\SmsProvider;
use HusamTariq\FilamentSmsSender\Models\OtpCode;
use HusamTariq\FilamentSmsSender\Services\OtpManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmsService
{
    protected Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Send an SMS message using the default provider or specified provider name
     * Returns array: ['success' => bool, 'code' => int|null, 'body' => string|null]
     */
    public function send(string $recipient, string $message, ?string $providerName = null): array
    {
        $provider = $this->getProvider($providerName);

        if (!$provider) {
            Log::error('No SMS provider available', [
                'requested_provider' => $providerName,
                'recipient' => $recipient,
            ]);
            return ['success' => false, 'code' => null, 'body' => 'No SMS provider available'];
        }

        return $this->sendWithProvider($recipient, $message, $provider);
    }

    /**
     * Send an SMS message using a specific SmsProvider instance
     * Returns array: ['success' => bool, 'code' => int|null, 'body' => string|null]
     */
    public function sendWithProvider(string $recipient, string $message, SmsProvider $provider): array
    {
        try {
            $gatewayConfig = $provider->getGatewayConfig();

            if (empty($gatewayConfig['endpoint'])) {
                Log::error('SMS Gateway endpoint not configured', [
                    'provider' => $provider->name,
                    'recipient' => $recipient,
                ]);
                return ['success' => false, 'code' => null, 'body' => 'SMS Gateway endpoint not configured'];
            }

            $requestData = $this->buildRequest($recipient, $message, $gatewayConfig);

            $response = $this->makeHttpRequest($requestData);
            $code = $response->getStatusCode();
            $body = (string) $response->getBody();

            // Check success conditions
            $success = false;
            $expectedCode = $provider->success_code ?? 200;
            $expectedBody = $provider->success_body ?? '';
            $conditional = $provider->success_conditional_body ?? '=';

            if ($code == $expectedCode) {
                switch ($conditional) {
                    case '=':
                        $success = trim($body) == trim($expectedBody);
                        break;
                    case '>':
                        $success = is_numeric($body) && is_numeric($expectedBody) && ((float) $body > (float) $expectedBody);
                        break;
                    case '<':
                        $success = is_numeric($body) && is_numeric($expectedBody) && ((float) $body < (float) $expectedBody);
                        break;
                    case 'like':
                        $success = stripos($body, $expectedBody) !== false;
                        break;
                    default:
                        $success = false;
                }
            }

            Log::info('SMS sent', [
                'provider' => $provider->name,
                'recipient' => $recipient,
                'status_code' => $code,
                'body' => $body,
                'success' => $success,
            ]);

            return ['success' => $success, 'code' => $code, 'body' => $body];
        } catch (RequestException $e) {
            $code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            $body = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error('Failed to send SMS', [
                'provider' => $provider->name,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'response' => $body,
            ]);
            return ['success' => false, 'code' => $code, 'body' => $body];
        } catch (\Exception $e) {
            Log::error('Unexpected error sending SMS', [
                'provider' => $provider->name,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'code' => null, 'body' => $e->getMessage()];
        }
    }

    /**
     * Send an OTP code using default provider or specified provider name
     */
    public function sendOtp(string $recipient, ?string $identifier = null, ?string $providerName = null): string|false
    {
        $provider = $this->getProvider($providerName);

        if (!$provider) {
            Log::error('No SMS provider available for OTP', [
                'requested_provider' => $providerName,
                'recipient' => $recipient,
            ]);
            return false;
        }

        return $this->sendOtpWithProvider($recipient, $identifier, $provider);
    }

    /**
     * Send an OTP code using a specific SmsProvider instance
     */
    public function sendOtpWithProvider(string $recipient, ?string $identifier, SmsProvider $provider): string|false
    {
        $otpManager = app(OtpManager::class);

        if (!$otpManager->canSend($recipient)) {
            Log::warning('OTP rate limit exceeded', ['recipient' => $recipient]);
            return false;
        }

        // Generate new OTP with provider-specific settings
        $otpCode = $otpManager->generateWithProvider($recipient, $identifier, $provider);

        // Prepare SMS message using provider's template
        $message = $otpManager->formatMessageWithProvider($otpCode, $provider);

        // Send SMS using the specified provider
        if ($this->sendWithProvider($recipient, $message, $provider)) {
            $otpManager->incrementRateLimit($recipient);
            return $otpCode;
        }

        return false;
    }

    /**
     * Verify an OTP code
     */
    public function verifyOtp(string $recipient, string $otpCode, ?string $identifier = null): bool
    {
        $otpManager = app(OtpManager::class);
        $result = $otpManager->verify($recipient, $otpCode, $identifier);

        Log::info('OTP verification attempt', [
            'recipient' => $recipient,
            'identifier' => $identifier,
            'success' => $result,
        ]);

        return $result;
    }

    /**
     * Get SMS provider by name or return default
     */
    protected function getProvider(?string $providerName = null): ?SmsProvider
    {
        if ($providerName) {
            return SmsProvider::getByName($providerName);
        }

        return SmsProvider::getDefault();
    }

    /**
     * Get gateway configuration from database (legacy support)
     * @deprecated Use SmsProvider instead
     */
    protected function getGatewayConfig(): array
    {
        return [];
    }

    /**
     * Build HTTP request data
     */
    protected function buildRequest(string $recipient, string $message, array $config): array
    {
        $method = strtoupper($config['method'] ?? 'POST');
        $endpoint = $config['endpoint'];
        $parameters = $config['parameters'] ?? [];
        $headers = $config['headers'] ?? [];

        // Replace placeholders in parameters
        $processedParameters = [];
        foreach ($parameters as $param) {
            $key = $param['key'] ?? '';
            $value = $param['value'] ?? '';

            if (empty($key))
                continue;

            // Replace placeholders
            $value = str_replace('{{ recipient }}', $recipient, $value);
            $value = str_replace('{{ message }}', $message, $value);

            $processedParameters[$key] = $value;
        }

        // Process headers
        $processedHeaders = [];
        foreach ($headers as $header) {
            $key = $header['key'] ?? '';
            $value = $header['value'] ?? '';

            if (empty($key))
                continue;

            $processedHeaders[$key] = $value;
        }

        return [
            'method' => $method,
            'endpoint' => $endpoint,
            'parameters' => $processedParameters,
            'headers' => $processedHeaders,
        ];
    }

    /**
     * Make the actual HTTP request
     */
    protected function makeHttpRequest(array $requestData): \Psr\Http\Message\ResponseInterface
    {
        $options = [
            'headers' => $requestData['headers'],
        ];

        if ($requestData['method'] === 'GET') {
            $url = $requestData['endpoint'];
            if (!empty($requestData['parameters'])) {
                $url .= '?' . http_build_query($requestData['parameters']);
            }
            return $this->httpClient->get($url, $options);
        } else {
            $options['form_params'] = $requestData['parameters'];
            return $this->httpClient->post($requestData['endpoint'], $options);
        }
    }

    /**
     * Generate a random OTP code
     */
    protected function generateOtp(int $length): string
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;

        return (string) random_int($min, $max);
    }
}

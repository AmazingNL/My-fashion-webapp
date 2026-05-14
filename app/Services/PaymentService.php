<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class PaymentService
{
    private string $currency;

    public function __construct()
    {
        $this->currency = strtolower((string) ($_ENV['PAYMENT_CURRENCY'] ?? 'eur'));
    }

    public function createPayment(string $provider, int $orderId, float $amount, string $returnUrl, array $items = []): array
    {
        return match ($this->normalizeProvider($provider)) {
            'stripe' => $this->createStripeCheckoutSession($orderId, $amount, $returnUrl, $items),
            'paypal' => $this->createPayPalOrder($orderId, $amount, $returnUrl),
            default => throw new RuntimeException('Unsupported payment provider.'),
        };
    }

    public function confirmPayment(string $provider, string $paymentReference, int $orderId, float $expectedAmount): array
    {
        return match ($this->normalizeProvider($provider)) {
            'stripe' => $this->confirmStripePayment($paymentReference, $orderId),
            'paypal' => $this->capturePayPalOrder($paymentReference, $orderId, $expectedAmount),
            default => throw new RuntimeException('Unsupported payment provider.'),
        };
    }

    public function isOnlineProvider(string $provider): bool
    {
        return in_array($this->normalizeProvider($provider), ['stripe', 'paypal'], true);
    }

    public function assertConfigured(string $provider): void
    {
        match ($this->normalizeProvider($provider)) {
            'stripe' => $this->requiredEnv('STRIPE_SECRET_KEY', 'Stripe'),
            'paypal' => $this->requiredEnv('PAYPAL_CLIENT_ID', 'PayPal') && $this->requiredEnv('PAYPAL_SECRET', 'PayPal'),
            default => true,
        };
    }

    private function createStripeCheckoutSession(int $orderId, float $amount, string $returnUrl, array $items): array
    {
        $secretKey = $this->requiredEnv('STRIPE_SECRET_KEY', 'Stripe');
        $successUrl = $this->appendQuery($returnUrl, [
            'payment' => 'success',
            'provider' => 'stripe',
            'orderId' => (string) $orderId,
            'session_id' => '{CHECKOUT_SESSION_ID}',
        ]);
        $cancelUrl = $this->appendQuery($returnUrl, [
            'payment' => 'cancelled',
            'provider' => 'stripe',
            'orderId' => (string) $orderId,
        ]);

        $body = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $orderId,
            'payment_method_types[0]' => 'card',
            'metadata[orderId]' => (string) $orderId,
            'line_items[0][price_data][currency]' => $this->currency,
            'line_items[0][price_data][unit_amount]' => (string) $this->amountToMinorUnits($amount),
            'line_items[0][price_data][product_data][name]' => 'Nuella Signet order #' . $orderId,
            'line_items[0][quantity]' => '1',
        ];

        $response = $this->request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/x-www-form-urlencoded',
        ], http_build_query($body));

        if (empty($response['url']) || empty($response['id'])) {
            throw new RuntimeException('Stripe did not return a checkout URL.');
        }

        return [
            'provider' => 'stripe',
            'reference' => (string) $response['id'],
            'redirectUrl' => (string) $response['url'],
        ];
    }

    private function confirmStripePayment(string $sessionId, int $orderId): array
    {
        $secretKey = $this->requiredEnv('STRIPE_SECRET_KEY', 'Stripe');
        $response = $this->request('GET', 'https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($sessionId), [
            'Authorization: Bearer ' . $secretKey,
        ]);

        $referenceOrderId = (int) ($response['client_reference_id'] ?? $response['metadata']['orderId'] ?? 0);
        if ($referenceOrderId !== $orderId) {
            throw new RuntimeException('Stripe payment does not match this order.');
        }

        return [
            'paid' => ($response['payment_status'] ?? '') === 'paid',
            'provider' => 'stripe',
            'reference' => $sessionId,
            'status' => (string) ($response['payment_status'] ?? 'unknown'),
        ];
    }

    private function createPayPalOrder(int $orderId, float $amount, string $returnUrl): array
    {
        $accessToken = $this->paypalAccessToken();
        $successUrl = $this->appendQuery($returnUrl, [
            'payment' => 'success',
            'provider' => 'paypal',
            'orderId' => (string) $orderId,
        ]);
        $cancelUrl = $this->appendQuery($returnUrl, [
            'payment' => 'cancelled',
            'provider' => 'paypal',
            'orderId' => (string) $orderId,
        ]);

        $response = $this->request('POST', $this->paypalBaseUrl() . '/v2/checkout/orders', [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ], json_encode([
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'custom_id' => (string) $orderId,
                'amount' => [
                    'currency_code' => strtoupper($this->currency),
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ]],
            'application_context' => [
                'return_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'user_action' => 'PAY_NOW',
            ],
        ], JSON_THROW_ON_ERROR));

        $approveUrl = '';
        foreach (($response['links'] ?? []) as $link) {
            if (($link['rel'] ?? '') === 'approve') {
                $approveUrl = (string) ($link['href'] ?? '');
                break;
            }
        }

        if ($approveUrl === '' || empty($response['id'])) {
            throw new RuntimeException('PayPal did not return an approval URL.');
        }

        return [
            'provider' => 'paypal',
            'reference' => (string) $response['id'],
            'redirectUrl' => $approveUrl,
        ];
    }

    private function capturePayPalOrder(string $paypalOrderId, int $orderId, float $expectedAmount): array
    {
        $accessToken = $this->paypalAccessToken();
        $response = $this->request('POST', $this->paypalBaseUrl() . '/v2/checkout/orders/' . rawurlencode($paypalOrderId) . '/capture', [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ], '{}');

        $purchaseUnit = $response['purchase_units'][0] ?? [];
        $referenceOrderId = (int) ($purchaseUnit['reference_id'] ?? $purchaseUnit['custom_id'] ?? 0);
        if ($referenceOrderId !== 0 && $referenceOrderId !== $orderId) {
            throw new RuntimeException('PayPal payment does not match this order.');
        }

        return [
            'paid' => ($response['status'] ?? '') === 'COMPLETED',
            'provider' => 'paypal',
            'reference' => $paypalOrderId,
            'status' => (string) ($response['status'] ?? 'unknown'),
            'amount' => $expectedAmount,
        ];
    }

    private function paypalAccessToken(): string
    {
        $clientId = $this->requiredEnv('PAYPAL_CLIENT_ID', 'PayPal');
        $secret = $this->requiredEnv('PAYPAL_SECRET', 'PayPal');
        $response = $this->request('POST', $this->paypalBaseUrl() . '/v1/oauth2/token', [
            'Authorization: Basic ' . base64_encode($clientId . ':' . $secret),
            'Content-Type: application/x-www-form-urlencoded',
        ], 'grant_type=client_credentials');

        if (empty($response['access_token'])) {
            throw new RuntimeException('PayPal access token request failed.');
        }

        return (string) $response['access_token'];
    }

    private function request(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 30,
            ]);

            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $raw = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error = curl_error($ch);

            if ($raw === false) {
                throw new RuntimeException('Payment request failed: ' . $error);
            }
        } else {
            $raw = file_get_contents($url, false, stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                    'content' => $body ?? '',
                    'timeout' => 30,
                    'ignore_errors' => true,
                ],
            ]));
            $statusLine = $http_response_header[0] ?? 'HTTP/1.1 500';
            preg_match('/\s(\d{3})\s/', $statusLine, $matches);
            $status = (int) ($matches[1] ?? 500);
        }

        $decoded = json_decode((string) $raw, true);
        if ($status >= 400) {
            $message = $decoded['error']['message'] ?? $decoded['message'] ?? 'Payment provider request failed.';
            throw new RuntimeException((string) $message);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function amountToMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function appendQuery(string $url, array $params): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . http_build_query($params);
    }

    private function paypalBaseUrl(): string
    {
        $mode = strtolower((string) ($_ENV['PAYPAL_MODE'] ?? 'sandbox'));
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    private function normalizeProvider(string $provider): string
    {
        return strtolower(trim($provider));
    }

    private function requiredEnv(string $key, string $provider): string
    {
        $value = trim((string) ($_ENV[$key] ?? ''));
        if ($value === '') {
            throw new RuntimeException($provider . ' is not configured. Add ' . $key . ' to your environment.');
        }

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

class EmailService
{
    private string $fromEmail;
    private string $fromName;
    private bool $enabled;
    private string $mailer;
    private bool $logEnabled;

    public function __construct()
    {
        $this->fromEmail = $this->env('MAIL_FROM_EMAIL', 'noreply@nuellasignet.com');
        $this->fromName = $this->env('MAIL_FROM_NAME', 'Nuella Signet');
        $this->enabled = $this->envBool('MAIL_ENABLED', true);
        $this->mailer = strtolower($this->env('MAIL_MAILER', 'log'));
        $this->logEnabled = $this->envBool('EMAIL_LOG_ENABLED', true);
    }

    /**
     * Send welcome email after registration
     */
    public function sendWelcomeEmail(string $to, string $firstName): bool
    {
        $subject = "Welcome to Afro Fashion!";
        $styles = $this->emailStyles();

        $message = "
        <html>
        <head>
            <style>
                {$styles}
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Afro Fashion!</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$firstName}!</h2>
                    <p>Thank you for registering with Afro Fashion. We're excited to have you!</p>
                    <p>You can now:</p>
                    <ul>
                        <li>Browse our exclusive collection of custom female clothing</li>
                        <li>Book appointments for custom designs</li>
                        <li>Track your orders</li>
                        <li>Manage your profile</li>
                    </ul>
                    <p>If you have any questions, please don't hesitate to contact us.</p>
                    <p>Happy shopping!</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Afro Fashion. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this->send($to, $subject, $message);
    }

    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(string $to, string $firstName, int $orderId, float $total, array $items): bool
    {
        $subject = "Order Confirmation - Order #{$orderId}";
        $styles = $this->emailStyles('
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #8B4789; color: white; }
            .total { font-size: 18px; font-weight: bold; text-align: right; }
        ');

        $itemsHtml = '';
        foreach ($items as $item) {
            $productName = htmlspecialchars((string) ($item['productName'] ?? $item['name'] ?? 'Product #' . ($item['productId'] ?? '')), ENT_QUOTES, 'UTF-8');
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            $itemsHtml .= "<tr>
                <td>{$productName}</td>
                <td>{$quantity}</td>
                <td>&euro;" . number_format($price, 2) . "</td>
                <td>&euro;" . number_format($price * $quantity, 2) . "</td>
            </tr>";
        }

        $message = "
        <html>
        <head>
            <style>
                {$styles}
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Order Confirmation</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$firstName}!</h2>
                    <p>Thank you for your order! We're getting it ready for shipment.</p>
                    <p><strong>Order Number:</strong> #{$orderId}</p>
                    
                    <h3>Order Details:</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                    </table>
                    
                    <p class='total'>Total: &euro;" . number_format($total, 2) . "</p>
                    
                    <p>We'll send you another email when your order ships.</p>
                    <p>If you have any questions about your order, please contact our customer service.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Afro Fashion. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this->send($to, $subject, $message);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $to, string $firstName, string $token, string $code): bool
    {
        $resetEndpoint = "POST /auth/password-reset/verify";
        $subject = "Password Reset Code";
        $styles = $this->emailStyles('
            .code { font-size: 22px; font-weight: bold; letter-spacing: 4px; text-align:center; padding: 10px; background:#fff; border-radius:8px; }
            .token { word-break: break-all; padding: 10px; background:#fff; border-radius:8px; }
        ');

        $message = "
    <html>
    <head>
        <style>
            {$styles}
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'><h1>Password Reset</h1></div>
            <div class='content'>
                <h2>Hello {$firstName}!</h2>
                <p>Use the code below to confirm your password reset:</p>
                <div class='code'>{$code}</div>

                <p>Reset token:</p>
                <p class='token'>{$token}</p>
                <p>Send the token, code, newPassword, and confirmPassword fields to {$resetEndpoint}.</p>
                <p>This code expires in 15 minutes.</p>
                <p>If you didn't request this, ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 Afro Fashion. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";

        return $this->send($to, $subject, $message);
    }


    /**
     * Send appointment confirmation email
     */
    public function sendAppointmentConfirmation(string $to, string $firstName, string $date, string $time, string $designType): bool
    {
        $subject = "Appointment Confirmation";
        $styles = $this->emailStyles('
            .details { background-color: white; padding: 15px; margin: 20px 0; border-left: 4px solid #8B4789; }
        ');

        $message = "
        <html>
        <head>
            <style>
                {$styles}
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Appointment Confirmed</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$firstName}!</h2>
                    <p>Your custom design appointment has been confirmed!</p>
                    
                    <div class='details'>
                        <p><strong>Date:</strong> {$date}</p>
                        <p><strong>Time:</strong> {$time}</p>
                        <p><strong>Design Type:</strong> {$designType}</p>
                    </div>
                    
                    <p>We look forward to seeing you and bringing your design vision to life!</p>
                    <p>If you need to reschedule, please contact us at least 24 hours in advance.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Afro Fashion. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this->send($to, $subject, $message);
    }

    public function sendPasswordChangedEmail(string $to, string $firstName): bool
    {
        $subject = "Password Changed Successfully";
        $styles = $this->emailStyles();

        $message = "
    <html>
    <head>
        <style>
            {$styles}
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'><h1>Password Updated</h1></div>
            <div class='content'>
                <h2>Hello {$firstName}!</h2>
                <p>Your password was changed successfully.</p>
                <p>If you did not do this, please contact support immediately.</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 Afro Fashion. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";

        return $this->send($to, $subject, $message);
    }


    /**
     * Send email - actual implementation
     */
    private function send(string $to, string $subject, string $message): bool
    {
        if (!$this->enabled) {
            return true;
        }

        $sent = match ($this->mailer) {
            'mailtrap' => $this->sendViaMailtrap($to, $subject, $message),
            default => false,
        };

        if ($this->logEnabled || !$sent) {
            $this->saveEmailLog($to, $subject, $message);
        }

        return $sent || $this->logEnabled;
    }

    private function sendViaMailtrap(string $to, string $subject, string $message): bool
    {
        $token = $this->env('MAILTRAP_API_TOKEN');
        if ($token === '') {
            return false;
        }

        $mode = strtolower($this->env('MAILTRAP_MODE', 'sandbox'));
        $endpoint = $mode === 'production'
            ? 'https://send.api.mailtrap.io/api/send'
            : 'https://sandbox.api.mailtrap.io/api/send/' . rawurlencode($this->env('MAILTRAP_INBOX_ID'));

        if ($mode !== 'production' && $this->env('MAILTRAP_INBOX_ID') === '') {
            return false;
        }

        $payload = json_encode([
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName,
            ],
            'to' => [[
                'email' => $to,
            ]],
            'subject' => $subject,
            'html' => $message,
            'category' => 'transactional',
        ], JSON_THROW_ON_ERROR);

        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => 20,
            ]);

            curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

            return $status >= 200 && $status < 300;
        }

        $raw = @file_get_contents($endpoint, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ]),
                'content' => $payload,
                'timeout' => 20,
                'ignore_errors' => true,
            ],
        ]));

        $statusLine = $http_response_header[0] ?? 'HTTP/1.1 500';
        preg_match('/\s(\d{3})\s/', $statusLine, $matches);
        $status = (int) ($matches[1] ?? 500);

        return $raw !== false && $status >= 200 && $status < 300;
    }

    private function saveEmailLog(string $to, string $subject, string $message): void
    {
        $logDir = __DIR__ . '/../../storage/emails';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $filename = $logDir . '/' . date('Y-m-d_His') . '_' . md5($to . $subject) . '.html';
        $content = "To: {$to}\nSubject: {$subject}\n\n{$message}";
        file_put_contents($filename, $content);
    }

    private function env(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value === false || $value === null ? $default : trim((string) $value);
    }

    private function envBool(string $key, bool $default): bool
    {
        $value = $this->env($key, $default ? 'true' : 'false');
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    private function emailStyles(string $extra = ''): string
    {
        return "
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #8B4789; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f4f4f4; padding: 20px; }
            .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
            {$extra}
        ";
    }


}

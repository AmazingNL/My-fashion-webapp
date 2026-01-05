<?php

declare(strict_types=1);

namespace App\Services;

class EmailService
{
    private string $fromEmail;
    private string $fromName;
    private bool $enabled;

    public function __construct()
    {
        $this->fromEmail = 'noreply@nuellasignet.com';
        $this->fromName = 'Nuella\'s Signet Fashion';
        // In production, set this to true when mail server is configured
        $this->enabled = true; // For demonstration, we'll log emails
    }

    /**
     * Send welcome email after registration
     */
    public function sendWelcomeEmail(string $to, string $firstName): bool
    {
        $subject = "Welcome to Nuella's Signet Fashion!";

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #8B4789; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f4f4f4; padding: 20px; }
                .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Nuella's Signet!</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$firstName}!</h2>
                    <p>Thank you for registering with Nuella's Signet Fashion. We're excited to have you!</p>
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
                    <p>&copy; 2025 Nuella's Signet Fashion. All rights reserved.</p>
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

        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= "<tr>
                <td>{$item['productName']}</td>
                <td>{$item['quantity']}</td>
                <td>\${$item['price']}</td>
                <td>\$" . number_format($item['price'] * $item['quantity'], 2) . "</td>
            </tr>";
        }

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #8B4789; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f4f4f4; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background-color: #8B4789; color: white; }
                .total { font-size: 18px; font-weight: bold; text-align: right; }
                .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
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
                    
                    <p class='total'>Total: \$" . number_format($total, 2) . "</p>
                    
                    <p>We'll send you another email when your order ships.</p>
                    <p>If you have any questions about your order, please contact our customer service.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Nuella's Signet Fashion. All rights reserved.</p>
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
    public function sendPasswordResetEmail(string $to, string $firstName, string $token): bool
    {
        $resetLink = "http://localhost/reset-password?token={$token}";
        $subject = "Password Reset Request";

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #8B4789; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f4f4f4; padding: 20px; }
                .button { display: inline-block; padding: 10px 20px; background-color: #8B4789; color: white; text-decoration: none; border-radius: 5px; }
                .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Password Reset</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$firstName}!</h2>
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' class='button'>Reset Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <p>{$resetLink}</p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request a password reset, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Nuella's Signet Fashion. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this->send($to, $subject, $message);
    }

    /**
     * Send appointment confirmation email
     */
    public function sendAppointmentConfirmation(string $to, string $firstName, string $date, string $time, string $designType): bool
    {
        $subject = "Appointment Confirmation";

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #8B4789; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f4f4f4; padding: 20px; }
                .details { background-color: white; padding: 15px; margin: 20px 0; border-left: 4px solid #8B4789; }
                .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
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
                    <p>&copy; 2025 Nuella's Signet Fashion. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this->send($to, $subject, $message);
    }

    /**
     * Send email - actual implementation
     */
    private function send(string $to, string $subject, string $message): bool
    {
        if (!$this->enabled) {
            return true; // Skip sending if disabled
        }

        // For demonstration, we'll log the email instead of actually sending
        // In production, use PHPMailer or similar
        $logDir = __DIR__ . '/../../storage/emails';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $filename = $logDir . '/' . date('Y-m-d_His') . '_' . md5($to . $subject) . '.html';
        $content = "To: {$to}\nSubject: {$subject}\n\n{$message}";
        file_put_contents($filename, $content);

        // Uncomment this in production with proper mail configuration
        /*
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>" . "\r\n";

        return mail($to, $subject, $message, $headers);
        */

        return true; // Return true for demonstration
    }
}
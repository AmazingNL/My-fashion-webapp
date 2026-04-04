<?php

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Services\IUserService;
use App\Services\EmailService;
use App\Services\IPasswordResetService;
use Exception;

class AuthController extends ControllerBase
{
    public function __construct(
        private IUserService $userService,
        private EmailService $emailService,
        private IPasswordResetService $passwordResetService
    ) {
    }

    public function showLogin()
    {
        $this->render('Auth/Login', ['title' => 'Login'], 'auth');
    }

    public function login(): void
    {
        $this->validateCsrf();
        $email = trim((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');
        try {
            if ($email === '' || $password === '') {
                $this->redirect('/?error=' . urlencode('Email and password are required.') . '&email=' . urlencode($email));
            }
            $user = $this->userService->authenticateUser($email, $password);
            if (!$user) {
                $this->redirect('/?error=' . urlencode('Invalid email or password.') . '&email=' . urlencode($email));
            }

            $_SESSION['userId'] = $user->userId;
            $_SESSION['role'] = $user->role;
            $redirect = ($_SESSION['role'] === 'admin') ? '/admin/dashboard' : '/productLists';
            $this->redirect($redirect);

        } catch (\Throwable $e) {
            $this->redirect('/?error=' . urlencode('An unexpected error occurred. Please try again later.') . '&email=' . urlencode($email));
        }
    }

    public function logout(): void
    {
        try {
            $this->ensureSession();
            $_SESSION = [];
            session_destroy();
            $this->redirect('/');
        }
        catch(Exception $e){
            $this->jsonResponse(['error' => 'An unexpected error occurred. Please try again later.'], 500);
        }

    }

    // ==========================
    // FORGOT PASSWORD FLOW
    // ==========================

    public function showForgotPassword(): void
    {
        $this->render('Auth/ForgotPassword', [
            'title' => 'Forgot Password'
        ], 'auth');
    }

    // user submits email + new password 
    public function requestReset(): void
    {
        try {
            $this->validateCsrf();
            $this->ensureSession();

            [$email, $newPassword, $confirm] = $this->readResetRequestInput();
            $errors = $this->validateResetRequestInput($email, $newPassword, $confirm);

            if ($errors) {
                $this->redirect($this->forgotPasswordUrl([
                    'error' => implode(' ', array_values($errors)),
                    'email' => $email,
                ]));
            }

            $user = $this->userService->getUserByEmail($email);
            if (!$user) {
                $this->redirect($this->forgotPasswordUrl([
                    'success' => 'If that email exists, we sent a code.',
                ]));
            }

            $reset = $this->passwordResetService->createReset((int) $user->userId);

            $_SESSION['pendingPasswordReset'] = [
                'token' => $reset['token'],
                'newPassword' => $newPassword,
                'userId' => (int) $user->userId,
            ];

            $this->sendPasswordResetEmail($user, $reset);

            $this->redirect($this->resetPasswordUrl((string) $reset['token'], [
                'success' => 'Verification code sent. Check your email.',
            ]));
        } catch (\Throwable $e) {
            $this->redirect($this->forgotPasswordUrl([
                'error' => 'Something went wrong. Please try again.',
            ]));
        }
    }

    // Step 2 page: enter the code
    public function showResetCode(): void
    {
        $token = (string) $this->input('token', '');
        $this->render('Auth/ResetCode', [
            'title' => 'Verify Reset Code',
            'token' => $token
        ], 'auth');
    }

    // Step 3: verify code -> then change password -> send confirmation -> redirect to /products
    public function verifyResetCode(): void
    {
        try {
            $this->validateCsrf();
            $this->ensureSession();

            $token = (string) $this->input('token', '');
            $code = trim((string) $this->input('code', ''));

            if ($token === '' || $code === '') {
                $this->redirect($this->resetPasswordUrl($token, [
                    'error' => 'Token and code are required.',
                ]));
            }

            $row = $this->passwordResetService->validate($token, $code);
            if (!$row) {
                $this->redirect($this->resetPasswordUrl($token, [
                    'error' => 'Invalid or expired code/link.',
                ]));
            }

            $pending = $_SESSION['pendingPasswordReset'] ?? null;
            if (!$pending || ($pending['token'] ?? '') !== $token) {
                $this->redirect($this->forgotPasswordUrl([
                    'error' => 'Session expired. Please start again.',
                ]));
            }

            $userId = (int) $row['userId'];
            $user = $this->userService->getUserById($userId);

            $ok = $this->userService->changeUserPassword($userId, (string) $pending['newPassword']);
            if (!$ok) {
                $this->redirect($this->resetPasswordUrl($token, [
                    'error' => 'Failed to update password.',
                ]));
            }

            $this->passwordResetService->markUsed((int) $row['tokenId']);
            unset($_SESSION['pendingPasswordReset']);

            if ($user) {
                $this->emailService->sendPasswordChangedEmail(
                    (string) $user->email,
                    (string) $user->firstName
                );
            }

            $this->redirect('/?success=' . urlencode('Password updated successfully. Please log in.'));
        } catch (\Throwable $e) {
            $this->redirect($this->forgotPasswordUrl([
                'error' => 'Something went wrong. Please try again.',
            ]));
        }
    }

    private function readResetRequestInput(): array
    {
        return [
            strtolower(trim((string) $this->input('email', ''))),
            (string) $this->input('newPassword', ''),
            (string) $this->input('confirmPassword', ''),
        ];
    }

    private function validateResetRequestInput(string $email, string $newPassword, string $confirm): array
    {
        $errors = [];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email.';
        }
        if ($newPassword === '' || $confirm === '') {
            $errors['password'] = 'Password fields are required.';
        }
        if ($newPassword !== $confirm) {
            $errors['confirmPassword'] = 'Passwords do not match.';
        }
        if (strlen($newPassword) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if (!preg_match('/[a-zA-Z]/', $newPassword)) {
            $errors['password'] = 'Password must contain at least one letter.';
        }
        if (!preg_match('/\d/', $newPassword)) {
            $errors['password'] = 'Password must contain at least one number.';
        }

        return $errors;
    }

    private function sendPasswordResetEmail(mixed $user, array $reset): void
    {
        $this->emailService->sendPasswordResetEmail(
            (string) $user->email,
            (string) $user->firstName,
            (string) $reset['token'],
            (string) $reset['code']
        );
    }

    private function forgotPasswordUrl(array $params = []): string
    {
        return '/forgotPassword' . ($params ? '?' . http_build_query($params) : '');
    }

    private function resetPasswordUrl(string $token, array $params = []): string
    {
        $query = array_merge(['token' => $token], $params);
        return '/reset-password?' . http_build_query($query);
    }
}

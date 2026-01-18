<?php

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Services\IUserService;
use App\Services\EmailService;
use App\Services\IPasswordResetService;

class AuthController extends ControllerBase
{
    public function __construct(
        private IUserService $userService,
        private EmailService $emailService,
        private IPasswordResetService $passwordResetService
    ) {}

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
                $this->jsonResponse(['error' => 'Email and password are required.'], 400);
            }

            $user = $this->userService->authenticateUser($email, $password);

            if (!$user) {
                $this->jsonResponse(['error' => 'Invalid email or password.'], 401);
            }

            $_SESSION['userId'] = $user->getUserId();
            $_SESSION['role'] = $user->getRole();

            $redirect = ($_SESSION['role'] === 'admin') ? '/admin/dashboard' : '/products';
            $this->jsonResponse(['ok' => true, 'redirect' => $redirect], 200);

        } catch (\Throwable $e) {
            $this->jsonResponse(['error' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }

    public function logout(): void
    {
        $this->ensureSession();
        $_SESSION = [];
        session_destroy();
        $this->redirect('/');
    }

    // ==========================
    // FORGOT PASSWORD FLOW
    // ==========================

    public function showForgotPassword(): void
    {
        $this->render('Auth/ForgotPassword', [
            'title' => 'Forgot Password',
            'pageScript' => 'forgotPassword.js'
        ], 'auth');
    }

    // Step 1: user submits email + new password (we DON'T change it yet)
    public function requestReset(): void
    {
        $this->validateCsrf();
        $this->ensureSession();

        $email = strtolower(trim((string)$this->input('email', '')));
        $newPassword = (string)$this->input('newPassword', '');
        $confirm = (string)$this->input('confirmPassword', '');

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email.';
        if ($newPassword === '' || $confirm === '') $errors['password'] = 'Password fields are required.';
        if ($newPassword !== $confirm) $errors['confirmPassword'] = 'Passwords do not match.';
        if (strlen($newPassword) < 8) $errors['password'] = 'Password must be at least 8 characters.';
        if (!preg_match('/[a-zA-Z]/', $newPassword)) $errors['password'] = 'Password must contain at least one letter.';
        if (!preg_match('/\d/', $newPassword)) $errors['password'] = 'Password must contain at least one number.';

        if ($errors) $this->jsonResponse(['errors' => $errors], 422);

        // Don't reveal if email exists
        $user = $this->userService->getUserByEmail($email);
        if (!$user) {
            $this->jsonResponse(['ok' => true, 'message' => 'If that email exists, we sent a code.', 'redirect' => '/'], 200);
        }

        // create token + code in DB
        $reset = $this->passwordResetService->createReset((int)$user->getUserId());

        // store pending password in session until code is verified
        $_SESSION['pendingPasswordReset'] = [
            'token' => $reset['token'],
            'newPassword' => $newPassword,
            'userId' => (int)$user->getUserId()
        ];

        // send email with code + link
        $this->emailService->sendPasswordResetEmail(
            (string)$user->getEmail(),
            (string)$user->getFirstName(),
            (string)$reset['token'],
            (string)$reset['code']
        );

        $this->jsonResponse([
            'ok' => true,
            'redirect' => '/reset-password?token=' . urlencode($reset['token'])
        ], 200);
    }

    // Step 2 page: enter the code
    public function showResetCode(): void
    {
        $token = (string)$this->input('token', '');
        $this->render('Auth/ResetCode', [
            'title' => 'Verify Reset Code',
            'token' => $token,
            'pageScript' => 'resetCode.js'
        ], 'auth');
    }

    // Step 3: verify code -> then change password -> send confirmation -> redirect to /products
    public function verifyResetCode(): void
    {
        $this->validateCsrf();
        $this->ensureSession();

        $token = (string)$this->input('token', '');
        $code = trim((string)$this->input('code', ''));

        if ($token === '' || $code === '') {
            $this->jsonResponse(['error' => 'Token and code are required.'], 400);
        }

        $row = $this->passwordResetService->validate($token, $code);
        if (!$row) {
            $this->jsonResponse(['error' => 'Invalid or expired code/link.'], 400);
        }

        $pending = $_SESSION['pendingPasswordReset'] ?? null;
        if (!$pending || ($pending['token'] ?? '') !== $token) {
            $this->jsonResponse(['error' => 'Session expired. Please start again.'], 400);
        }

        $userId = (int)$row['userId'];
        $user = $this->userService->getUserById($userId);

        $ok = $this->userService->changeUserPassword($userId, (string)$pending['newPassword']);
        if (!$ok) {
            $this->jsonResponse(['error' => 'Failed to update password.'], 500);
        }

        $this->passwordResetService->markUsed((int)$row['tokenId']);
        unset($_SESSION['pendingPasswordReset']);

        if ($user) {
            $this->emailService->sendPasswordChangedEmail(
                (string)$user->getEmail(),
                (string)$user->getFirstName()
            );
        }

        $this->jsonResponse(['ok' => true, 'redirect' => '/'], 200);
    }
}

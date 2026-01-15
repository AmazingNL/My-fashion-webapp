<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Core\ControllerBase;

class AuthController extends ControllerBase
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function showLogin()
    {
        $this->render(
            'Auth/Login',
            ['title' => 'Login'],
            'auth'
        );
    }

    public function login(): void
    {
        $this->validateCsrf();

        $email = trim((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');

        try {
            if ($email === '' || $password === '') {
                $this->jsonResponse(['error' => 'Email and password are required.'], 400);
                return;
            }

            $user = $this->userService->authenticateUser($email, $password);

            if (!$user) {
                $this->jsonResponse(['error' => 'Invalid email or password.'], 401);
                return;
            }

            $_SESSION['userId'] = $user->getUserId();
            $_SESSION['role'] = $user->getRole();

            $redirect = ($_SESSION['role'] === 'admin')
                ? '/admin/dashboard'
                : '/products';

            $this->jsonResponse(['ok' => true, 'redirect' => $redirect], 200);
            return;

        } catch (\Throwable $e) {
            $this->jsonResponse(
                ['error' => 'An unexpected error occurred. Please try again later.'],
                500
            );
            return;
        }
    }



    public function logout(): void
    {
        $this->ensureSession();      // safer than session_start() here
        $_SESSION = [];
        session_destroy();

        $this->redirect('/');        // your login page
    }

}
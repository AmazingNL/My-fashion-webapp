<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Core\ControllerBase;

class AuthController extends ControllerBase {
    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function showLogin() {
        $this->render('Users/Login', ['title' => 'Login']);
    }

    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($this->input('email', ''));
        $password = (string) $this->input('password', '');

        try {
            if (empty($email) || empty($password)) {
                $this->jsonResponse(['error' => 'Email and password are required.'], 400);
                return;
            }
            $result = $this->userService->authenticateUser($email, $password);
            if ($result instanceof \App\Models\User) {
            $_SESSION['user_id'] = $result->getId();
            $this->redirect('/productCatalogues');
            return;
            } else {
            // Render login form with error
            $this->jsonResponse(['error' => 'Invalid email or password.'], 401);
            return;
            }
        }catch (\Throwable $e) {
            $this->jsonResponse(
                ['error' => 'An unexpected error occurred. Please try again later.'],
                500
            );
            return;
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }
}
<?php

namespace App\Controllers;

use App\models\User;
use App\services\UserService;
use App\Core\ControllerBase;

class UserController extends ControllerBase
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function showRegistrationForm(): void
    {
        // Render registration form view
        $this->render('Users/showRegistrationForm', ['title' => 'Register']);
    }

    public function registerUser(): void
    {
        $user = new User(
            null,
            trim($this->input('firstName', '')),
            trim($this->input('lastName', '')),
            trim($this->input('email', '')),
            '', // password hashed in service
            trim($this->input('phone', '')),
            'customer',
            null,
            null
        );

        $password = (string) $this->input('password', '');

        try {
            $errors = $this->userService->createUser($user, $password);

            if (!empty($errors)) {
                // Service validation errors → frontend fetch
                $this->jsonResponse(
                    ['errors' => $errors],
                    422
                );
                return;
            }

            // Success → frontend fetch
            $this->jsonResponse(
                ['message' => 'User registered successfully.'],
                201
            );
        } catch (\Throwable $e) {
            // Safety net (DB down, unexpected error, etc.)
            $this->jsonResponse(
                ['errors' => [$e->getMessage()]],
                500
            );
        }

    }

    public function getUserProfile($id): ?User
    {
        try {
            return $this->userService->getUserById($id);
        } catch (\Exception $e) {
            return null;
        }
    }


}


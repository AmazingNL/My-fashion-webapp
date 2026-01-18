<?php

namespace App\Controllers;

use App\models\User;
use App\services\IUserService;
use App\Core\ControllerBase;
use App\services\IActivityLogService;

class UserController extends ControllerBase
{
    private IUserService $userService;

    private IActivityLogService $activityLogService;

    public function __construct(IUserService $userService, IActivityLogService $activityLogService)
    {
        $this->userService = $userService;
        $this->activityLogService = $activityLogService;
    }

    public function showRegistrationForm(): void
    {
        // Render registration form view
        $this->render(
            'Users/showRegistrationForm',
            ['title' => 'Register'],
            'main'
        );
    }

    public function registerUser(): void
    {
        try {
            [$user, $password] = $this->registrationInput();
            $this->createUserOrFail($user, $password);
            $this->logRegistration($user);
            $this->jsonResponse(['message' => 'User registered successfully.'], 201);
            $this->redirect('/');
        } catch (\Throwable $e) {
            $this->handleRegistrationError($e);
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

    public function aboutUs()
    {
        $this->render('/About/Index', ['title' => 'About Us'], 'main');
    }


    // Private and Helper Functions //
    private function registrationInput(): array
    {
        return [
            new User(
                null,
                trim($this->input('firstName', '')),
                trim($this->input('lastName', '')),
                trim($this->input('email', '')),
                '',
                trim($this->input('phone', '')),
                'customer',
                null,
                null
            ),
            (string) $this->input('password', '')
        ];
    }

    private function createUserOrFail(User $user, string $password): void
    {
        $errors = $this->userService->createUser($user, $password);

        if (empty($errors))
            return;

        $this->jsonResponse(['errors' => $errors], 422);
        exit;
    }

    private function logRegistration(User $user): void
    {
        $this->activityLogService->log(
            $user->getUserId(),
            'Registered new user: ' . $user->getEmail(),
            'user_registration',
            null,
            'A new user has been registered.'
        );
    }

    private function handleRegistrationError(\Throwable $e): void
    {
        error_log((string) $e);
        $this->jsonResponse(['errors' => ['Registration failed']], 500);
    }


}


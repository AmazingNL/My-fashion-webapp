<?php

namespace App\Controllers;

use App\models\User;
use App\services\IUserService;
use App\Core\ControllerBase;

class UserController extends ControllerBase
{
    private IUserService $userService;

    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    public function showRegistrationForm(): void
    {
        $this->render(
            'Users/showRegistrationForm',
            ['title' => 'Register'],
            'main'
        );
    }

    public function registerUser(): void
    {
        try {
            [$user, $password, $oldInput] = $this->registrationInput();
            $errors = $this->userService->createUser($user, $password);
            if (!empty($errors)) {
                $this->render(
                    'Users/ShowRegistrationForm',
                    [
                        'title' => 'Register',
                        'errors' => $errors,
                        'oldInput' => $oldInput,
                    ],
                    'main'
                );
                return;
            }
            $this->redirect('/productLists');
        } catch (\Throwable $e) {
            $this->render(
                'Users/ShowRegistrationForm',
                [
                    'title' => 'Register',
                    'errors' => ['Registration failed. Please try again.'.$e],
                    'oldInput' => [],
                ],
                'main'
            );
        }
    }
    // Private and Helper Functions //
    private function registrationInput(): array
    {
        $firstName = trim((string) $this->input('firstName', ''));
        $lastName = trim((string) $this->input('lastName', ''));
        $email = trim((string) $this->input('email', ''));
        $phone = trim((string) $this->input('phone', ''));
        $password = (string) $this->input('password', '');

        return [
            new User(
                null,
                $firstName,
                $lastName,
                $email,
                '',
                $phone,
                'customer',
                null,
                null
            ),
            $password,
            [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'phone' => $phone,
            ]
        ];
    }


}


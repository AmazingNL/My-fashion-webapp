<?php

namespace App\Controllers;

use App\Mappers\RegistrationRequestMapper;
use App\Mappers\ResponseUserMapper;
use App\Services\Interfaces\IUserService;
use App\Core\ControllerBase;

class UserController extends ControllerBase
{
    private IUserService $userService;

    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    public function viewUsers(): void
    {
        try {
            $this->jsonResponse($this->success([
                'users' => ResponseUserMapper::responseUserMappers($this->userService->getAllUsers()),
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load users.'), 500);
        }
    }

    public function aboutUs(): void
    {
        try {
            $this->jsonResponse($this->success([
                'name' => 'My Fashion Webapp',
                'type' => 'fashion ecommerce API',
            ]));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Failed to load about endpoint.'), 500);
        }
    }

    public function registerUser(): void
    {
        try {
            [$user, $password, $oldInput] = $this->registrationInput();
            $errors = $this->userService->createUser($user, $password);
            if (!empty($errors)) {
                $this->jsonResponse($this->error('Registration validation failed.', $errors, [
                    'oldInput' => $oldInput,
                ]), 422);
            }
            $this->jsonResponse($this->success([
                'user' => ResponseUserMapper::responseUserMapper($user),
            ], 'Registration successful.'), 201);
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Registration failed. Please try again.'), 500);
        }
    }
    // Private and Helper Functions //
    private function registrationInput(): array
    {
        $dto = RegistrationRequestMapper::mapToRegistrationRequestDto($this->requestData());

        return [
            RegistrationRequestMapper::mapToUser($dto),
            $dto->password,
            RegistrationRequestMapper::mapToOldInput($dto)
        ];
    }


}


<?php

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Mappers\LoginMapper;
use App\Mappers\PasswordResetRequestMapper;
use App\Mappers\ResponseUserMapper;
use App\Services\IUserService;
use App\Services\EmailService;
use App\Services\IPasswordResetService;
use Firebase\JWT\JWT;

class AuthController extends ControllerBase
{
    public function __construct(
        private IUserService $userService,
        private EmailService $emailService,
        private IPasswordResetService $passwordResetService
    ) {
    }

    public function login(): void
    {
        try {
            $loginDto = LoginMapper::mapToLoginDto($this->requestData());
            $user = $this->userService->authenticateUser($loginDto);
            if (!$user) {
                $this->jsonResponse($this->error('Invalid email or password.'), 401);
                return;
            }
            // Prepare JWT payload
            $payload = [
                "iss" => "your-domain.com",
                "iat" => time(),
                "exp" => time() + 3600, // Token expires in 1 hour
                "sub" => $user->userId,
                "role" => $user->role
            ];
            $jwt = JWT::encode($payload, $this->jwtCode(), 'HS256');
            $userDto = ResponseUserMapper::responseUserMapper($user);
            $this->jsonResponse($this->success([
                'token' => $jwt,
                'user' => $userDto,
            ], 'Logged in successfully.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('An unexpected error occurred. Please try again later.'), 500);
        }
    }

    public function logout(): void
    {
        try {
            $this->jsonResponse($this->success(null, 'Logged out successfully.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('An unexpected error occurred. Please try again later.'), 500);
        }

    }

    public function requestReset(): void
    {
        try {
            $dto = PasswordResetRequestMapper::mapToPasswordResetRequestDto($this->requestData());
            $errors = $this->userService->validateResetEmail($dto->email);

            if ($errors) {
                $this->jsonResponse($this->error('Password reset validation failed.', $errors, [
                    'email' => $dto->email,
                ]), 422);
            }

            $user = $this->userService->getUserByEmail($dto->email);
            if (!$user) {
                $this->jsonResponse($this->success(null, 'If that email exists, we sent a code.'));
            }

            $reset = $this->passwordResetService->createReset((int) $user->userId);

            $this->sendPasswordResetEmail($user, $reset);

            $this->jsonResponse($this->success([
                'token' => (string) $reset['token'],
            ], 'Verification code sent. Check your email.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Something went wrong. Please try again.'), 500);
        }
    }

    public function verifyResetCode(): void
    {
        try {
            $dto = PasswordResetRequestMapper::mapToPasswordResetVerifyDto($this->requestData());

            if ($dto->token === '' || $dto->code === '') {
                $this->jsonResponse($this->error('Token and code are required.'), 422);
            }

            $errors = $this->userService->validateNewPassword($dto->newPassword, $dto->confirmPassword);
            if ($errors) {
                $this->jsonResponse($this->error('Password reset validation failed.', $errors), 422);
            }

            $row = $this->passwordResetService->validate($dto->token, $dto->code);
            if (!$row) {
                $this->jsonResponse($this->error('Invalid or expired code/link.'), 422);
            }

            $userId = (int) $row['userId'];
            $user = $this->userService->getUserById($userId);

            $ok = $this->userService->changeUserPassword($userId, $dto->newPassword);
            if (!$ok) {
                $this->jsonResponse($this->error('Failed to update password.'), 500);
            }

            $this->passwordResetService->markUsed((int) $row['tokenId']);

            if ($user) {
                $this->emailService->sendPasswordChangedEmail(
                    (string) $user->email,
                    (string) $user->firstName
                );
            }

            $this->jsonResponse($this->success(null, 'Password updated successfully. Please log in.'));
        } catch (\Throwable $e) {
            $this->jsonResponse($this->error('Something went wrong. Please try again.'), 500);
        }
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

}

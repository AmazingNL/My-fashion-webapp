<?php

namespace App\Services;

use App\Models\User;
use App\DTO\LoginDto;

interface IUserService {
    public function getAllUsers(): array;
    public function deleteUser(int $userId): bool;
    public function createUser(User $user, string $password): array;
    public function authenticateUser(LoginDto $loginDto): ?User;
    public function validateResetEmail(string $email): array;
    public function validateNewPassword(string $newPassword, string $confirm): array;
    public function getUserById(int $id): ?User;
    public function getUserByEmail(string $email): ?User;
    public function changeUserPassword(int $id, string $newPassword): bool;
}

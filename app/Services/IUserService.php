<?php

namespace App\Services;

use App\Models\User;

interface IUserService {
    public function getAllUsers(): array;
    public function deleteUser(int $userId): bool;
    public function createUser(User $user, string $password): array;
    public function authenticateUser(string $email, string $password): ?User;
    public function getUserById(int $id): ?User;
    public function getUserByEmail(string $email): ?User;
    public function changeUserPassword(int $id, string $newPassword): bool;
}

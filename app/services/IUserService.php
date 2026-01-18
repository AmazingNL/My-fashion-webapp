<?php

namespace App\Services;

use App\Models\User;

interface IUserService {
    public function getAllUsers(): array;
        public function updateUserStatus(int $userId, bool $isActive): bool;
    public function createUser(User $user, string $password): array;
    public function authenticateUser($email, $password): ?User;
    public function getUserById($id): ?User;
    public function getUserByEmail($email): ?User;
    public function updateUser(User $user): bool;
    public function changeUserPassword($id, $newPassword): bool;
    public function changeUserEmail($id, $newEmail): bool;
    public function deleteUser($id): bool;
}

<?php

namespace App\Repositories;

use App\Models\User;

interface IUserRepository
{

    public function getAll(): array;
    public function findById($id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
    public function deleteUser(int $userId): bool;
    public function changePassword(int $id, string $newPassword): bool;

}

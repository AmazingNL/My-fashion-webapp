<?php

namespace App\Repositories;

use App\Models\User;

interface IUserRepository {

    public function getAll(): array;
    public function findById($id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
    public function update(User $user): void;
    public function delete($id): void;
    public function changePassword($id, $newPassword): void;
    public function changeEmail($id, $newEmail): void;  

    // Define methods for user repository
}

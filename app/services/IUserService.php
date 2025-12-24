<?php

namespace app\services;

use app\models\User;

interface IUserService {
    public function getAllUsers(): array;
    public function createUser(User $user): bool;
    public function authenticateUser($email, $password): ?User;
    public function getUserById($id): ?User;
    public function updateUser(User $user): bool;
    public function changeUserPassword($id, $newPassword): bool;
    public function changeUserEmail($id, $newEmail): bool;
    public function deleteUser($id): bool;
}

<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\IUserRepository;
use DateTime;
use Exception;

class UserService implements IUserService
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function createUser(User $user, string $password): array
    {
        $errors = $this->validatePassword($password);
        if (!empty($errors)) {
            return $errors;
        }
        $email = $this->normalizeEmail($user->email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['email' => 'Please enter a valid email address.'];
        }
        if ($this->userRepository->findByEmail($email) !== null) {
            return ['email' => 'An account with this email already exists.'];
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userToSave = new User(
            $user->userId,
            $user->firstName,
            $user->lastName,
            $email,
            $hashedPassword,
            $user->phone,
            $user->role,
            new DateTime(),
            new DateTime()
        );
        $this->userRepository->save($userToSave);
        return [];
    }

    private function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
            return $errors;
        }

        if (!preg_match('/[a-zA-Z]/', $password)) {
            $errors['password'] = 'Password must contain at least one letter.';
            return $errors;
        }

        if (!preg_match('/\d/', $password)) {
            $errors['password'] = 'Password must contain at least one digit.';
            return $errors;
        }

        return $errors;
    }

    public function authenticateUser(string $email, string $password): ?User
    {
        $email = $this->normalizeEmail($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return null;
        }
        if (!password_verify($password, $user->password)) {
            return null;
        }
        return $user;
    }


    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function getUserByEmail(string $email): ?User
    {
        $email = $this->normalizeEmail($email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $this->userRepository->findByEmail($email);
    }

    public function deleteUser(int $userId): bool
    {
        return $this->userRepository->deleteUser($userId);
    }



    public function changeUserPassword(int $id, string $newPassword): bool
    {
        return $this->userRepository->changePassword($id, $newPassword);
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->getAll();
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }


}
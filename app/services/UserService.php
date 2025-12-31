<?php

namespace App\Services;

use App\Models\User;
use App\Services\IUserService;
use App\Repositories\IUserRepository;
use App\Core\RepositoryBase;
use App\Core\ControllerBase;
use DateTime;

class UserService extends RepositoryBase implements IUserService
{
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(User $user, string $password): array
    {
        if ($user !== null) {
            $existingUser = $this->userRepository->findByEmail($user->getEmail());
            if ($existingUser !== null) {
                return ['email' => 'Email is already registered.'];
            }
        }
        $errors = $this->validatePassword($password);

        if (!empty($errors)) {
            return $errors; // controller will jsonResponse these
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $userToSave = new User(
            $user->getUserId(),
            $user->getFirstName(),
            $user->getLastName(),
            strtolower(trim($user->getEmail())),
            $hashedPassword,
            $user->getPhone(),
            $user->getRole(),
            new DateTime(),
            new DateTime()
        );

        $this->userRepository->save($userToSave);

        return []; // no errors
    }

    private function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
            return $errors; // early return is fine
        }

        // at least one letter (upper OR lower)
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

    public function authenticateUser($email, $password): ?User
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $user = $this->userRepository->findByEmail($email);
        if (empty($user)) {                 
            return null;
        }

        if (!password_verify($password, $user->getPassword())) {
            return null;
        }

        return $user;
    }


    public function getUserById($id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function getUserByEmail($email): ?User
    {
        try {
            return $this->userRepository->findByEmail($email);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateUser(User $user): bool
    {
        try {
            $this->userRepository->update($user);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function changeUserPassword($id, $newPassword): bool
    {
        try {
            $this->userRepository->changePassword($id, $newPassword);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function changeUserEmail($id, $newEmail): bool
    {
        try {
            $this->userRepository->changeEmail($id, $newEmail);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function deleteUser($id): bool
    {
        try {
            $this->userRepository->delete($id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAllUsers(): array
    {
        try {
            return $this->userRepository->getAll();
        } catch (\Exception $e) {
            return [];
        }
    }


}
<?php

namespace app\services;

use app\models\User;
use app\services\IUserService;
use app\repositories\IUserRepository;

class UserService extends RepositoryBase implements IUserService {
    private IUserRepository $userRepository;

    public function __construct(IUserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function createUser(User $user, $password): bool {
        try {
            $hashedPassword = $this->hashPassword($password);
            $userToSave = new User(
            $user->getId(),             
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail(),
            $hashedPassword,
            $user->getPhone(),
            $user->getRole(),
            $now,                        // createdAt
            $now                         // updatedAt
        );
            $this->userRepository->save($userToSave);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function hashPassword($password): string {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function authenticateUser($email, $password): ?User {
        try {
            $user = $this->userRepository->findByEmail($email);
            if ($user && password_verify($password, $user->getPassword())) {
                return $user;
            }
        } catch (\Exception $e) {
            return null;
        }

    }

    public function getUserById($id): ?User {
        return $this->userRepository->findById($id);
    }

    public function updateUser(User $user): bool {
        try {
            $this->userRepository->update($user);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function changeUserPassword($id, $newPassword): bool {
        try {
            $this->userRepository->changePassword($id, $newPassword);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function changeUserEmail($id, $newEmail): bool {
        try {
            $this->userRepository->changeEmail($id, $newEmail);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function deleteUser($id): bool {
        try {
            $this->userRepository->delete($id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getAllUsers(): array {
        try {
            return $this->userRepository->getAll();
        } catch (\Exception $e) {
            return [];
        }
    }


}
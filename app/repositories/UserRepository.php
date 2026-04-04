<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\IUserRepository;
use App\Core\RepositoryBase;
use Exception;
use PDOException;
use RuntimeException;

class UserRepository extends RepositoryBase implements IUserRepository
{

    public function getAll(): array
    {
        try {
            $sql = "SELECT * FROM users ORDER BY createdAt DESC";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $users;
        } catch (PDOException $e) {
            throw new RuntimeException("DB error" . $e);
        }
    }


    public function findById($Id): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE userId = :userId LIMIT 1";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':userId' => $Id]);

            $stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, User::class);
            $user = $stmt->fetch();
            return $user instanceof User ? $user : null;
        } catch (PDOException $e) {
            throw new RuntimeException("Db error" . $e);
        }
    }


    public function findByEmail(string $email): ?User
    {
        try {
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([':email' => $email]);

            $stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, User::class);
            $user = $stmt->fetch();
            return $user instanceof User ? $user : null;
        } catch (PDOException $e) {
            throw new RuntimeException("Error occured while trying to fetch user" . $e->getMessage());
        }
    }

    public function save(User $user): void
    {
        try {
            $sql = "INSERT INTO users (firstName, lastName, role, createdAt, updatedAt, phone, email, password) 
            VALUES 
            (:firstName, :lastName, :role, NOW(), NOW(), :phone, :email, :password)";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':firstName' => $user->firstName,
                ':lastName' => $user->lastName,
                ':role' => $user->role,
                ':phone' => $user->phone,
                ':email' => $user->email,
                ':password' => $user->password
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException("failed to create user" . $e);
        }

    }

    public function deleteUser(int $userId): bool
    {
        try {
            $sql = "DELETE FROM users WHERE userId = :userId AND role <> 'admin'";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':userId' => $userId,
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("failed to delete user" . $e);
        }
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        try {
            $sql = "UPDATE users SET password = :password, updatedAt = NOW() WHERE userId = :userId";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute([
                ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
                ':userId' => $id,
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new RuntimeException("failed to change password" . $e);
        }
    }

}
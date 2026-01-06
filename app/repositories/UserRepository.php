<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\IUserRepository;
use App\Core\RepositoryBase;

class UserRepository extends RepositoryBase implements IUserRepository
{

    public function getAll(): array
    {
        $sql = "SELECT * FROM users ORDER BY createdAt DESC";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $users = [];

        foreach ($rows as $row) {
            $users[] = new User(
                (int) $row['userId'],
                (string) $row['firstName'],
                (string) $row['lastName'],
                (string) $row['email'],
                (string) $row['password'],
                (string) ($row['phone'] ?? ''),
                (string) $row['role'],
                $row['createdAt'] ?? null,
                $row['updatedAt'] ?? null
            );
        }

        return $users;
    }


    public function findById($Id): ?User
    {
        $sql = "SELECT * FROM users WHERE userId = :userId LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':userId' => $Id]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row)
            return null;

        return new User(
            (int) $row['userId'],
            (string) $row['firstName'],
            (string) $row['lastName'],
            (string) $row['email'],
            (string) $row['password'],
            (string) $row['phone'],
            (string) $row['role'],
            $row['createdAt'],
            $row['updatedAt']
        );
    }


    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row)
            return null;

        return new User(
            (int) $row['userId'],
            (string) $row['firstName'],
            (string) $row['lastName'],
            (string) $row['email'],
            (string) $row['password'],
            (string) $row['phone'],
            (string) $row['role'],
            $row['createdAt'],
            $row['updatedAt']
        );
    }




    public function save(User $user): void
    {
        // Implementation here
        $sql = "INSERT INTO users (firstName, lastName, phone, role, createdAt, updatedAt, email, password) 
        VALUES (:firstName, :lastName, :phone, :role, NOW(), NOW(), :email, :password)";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':firstName' => $user->getFirstName(),
            ':lastName' => $user->getLastName(),
            ':phone' => $user->getPhone(),
            ':role' => $user->getRole(),
            ':email' => $user->getEmail(),
            ':password' => $user->getPassword()
        ]);
    }

    public function update(User $user): void
    {
        // Implementation here
        $sql = "UPDATE users 
        SET firstName = :firstName, lastName = :lastName, phone = :phone, role = :role,
            updatedAt = NOW(), email = :email
        WHERE userId = :userId";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':firstName' => $user->getFirstName(),
            ':lastName' => $user->getLastName(),
            ':phone' => $user->getPhone(),
            ':role' => $user->getRole(),
            ':email' => $user->getEmail(),
            ':userId' => $user->getUserId()
        ]);
    }

    public function delete($id): void
    {
        // Implementation here
        $sql = "DELETE FROM users WHERE userId = :userId";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':userId' => $id]);
    }

    public function changePassword($id, $newPassword): void
    {
        // Implementation here
        $sql = "UPDATE users SET password = :password, updatedAt = NOW() WHERE userId = :userId";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':userId' => $id
        ]);
    }

    public function changeEmail($id, $newEmail): void
    {
        // Implementation here
        $sql = "UPDATE users SET email = :email, updatedAt = NOW() WHERE userId = :userId";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':email' => $newEmail,
            ':userId' => $id
        ]);
    }
}
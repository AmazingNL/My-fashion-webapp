<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\IUserRepository;
use App\Core\RepositoryBase;

class UserRepository extends RepositoryBase implements IUserRepository
{

    public function getAll(): array
    {
        // Implementation here
        $sql = "SELECT * FROM Users";
        $result = $this->getConnection()->query($sql);
        $users = $result->fetchAll(\PDO::FETCH_CLASS, User::class);
        return $users;
    }

    public function findById($id): ?User
    {
        // Implementation here
        $sql = "SELECT * FROM Users WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetchObject(User::class);

        return $user === false ? null : $user;
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM Users WHERE email = :email";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetchObject(User::class);

        return $user === false ? null : $user;
    }


    public function save(User $user): void
    {
        // Implementation here
        $sql = "INSERT INTO Users (firstName, lastName, phone, role, createdAt, updatedAt, email, password) 
        VALUES (:firstName, :lastName, :phone, :role, NOW(), NOW(), :email, :password)";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':firstName' => $user->getFirstName(),
            ':lastName' => $user->getLastName(),
            ':phone' => $user->getPhone(),
            ':role' => $user->getRole(),
            ':email' => $user->getEmail(),
            ':password' => password_hash($user->getPassword(), PASSWORD_DEFAULT)
        ]);
    }

    public function update(User $user): void
    {
        // Implementation here
        $sql = "UPDATE Users SET firstName = :firstName, lastName = :lastName, phone = :phone, role = :role, updatedAt = NOW(), email = :email WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':firstName' => $user->getFirstName(),
            ':lastName' => $user->getLastName(),
            ':phone' => $user->getPhone(),
            ':role' => $user->getRole(),
            ':email' => $user->getEmail(),
            ':id' => $user->getId()
        ]);
    }

    public function delete($id): void
    {
        // Implementation here
        $sql = "DELETE FROM Users WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    public function changePassword($id, $newPassword): void
    {
        // Implementation here
        $sql = "UPDATE Users SET password = :password, updatedAt = NOW() WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT),
            ':id' => $id
        ]);
    }

    public function changeEmail($id, $newEmail): void
    {
        // Implementation here
        $sql = "UPDATE Users SET email = :email, updatedAt = NOW() WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':email' => $newEmail,
            ':id' => $id
        ]);
    }
}
<?php

namespace App\Models;

class User {
    public ?int $userId = null;
    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $password = '';
    public string $phone = '';
    public string $role = 'customer';
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function __construct(
        ?int $userId = null,
        string $firstName = '',
        string $lastName = '',
        string $email = '',
        string $password = '',
        string $phone = '',
        string $role = 'customer',
        \DateTimeInterface|string|null $createdAt = null,
        \DateTimeInterface|string|null $updatedAt = null
    ) {

        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
        $this->role = $role;
        $this->createdAt = $createdAt instanceof \DateTimeInterface ? $createdAt->format('Y-m-d H:i:s') : $createdAt;
        $this->updatedAt = $updatedAt instanceof \DateTimeInterface ? $updatedAt->format('Y-m-d H:i:s') : $updatedAt;
    }


}
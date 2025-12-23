<?php

class User {
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $password;
    private $phone;
    private $role;
    private $createdAt;
    private $updatedAt;

    public function __construct($id, $firstName, $lastName, $email, $password, $phone, $role, $createdAt, $updatedAt) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
        $this->role = $role;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getEmail() {
        return $this->email;
    }
}
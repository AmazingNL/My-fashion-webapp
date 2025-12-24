<?php

namespace app\models;

use app\models\User;
use app\models\AppointmentStatus;

class Appointment {
    private $appointmentId;
    private $customerId;
    private $date;
    private AppointmentStatus $status;
    private $note;
    private $createdAt;
    private $updatedAt;

    public function __construct($appointmentId, User $customerId, $date, AppointmentStatus $status, $note, $createdAt, $updatedAt) {
        $this->appointmentId = $appointmentId;
        $this->customerId = $customerId->getId();
        $this->date = $date;
        $this->status = $status;
        $this->note = $note;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getAppointmentId() {
        return $this->appointmentId;
    }

    public function getCustomerId() {
        return $this->customerId;
    }

    public function getDate() {
        return $this->date;
    }

    public function getStatus(): AppointmentStatus {
        return $this->status;
    }

    // Helper to convert stored string (e.g. from DB) to the enum
    public static function statusFromName(string $name): AppointmentStatus {
        $name = strtoupper($name);
        foreach (AppointmentStatus::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }
        return AppointmentStatus::REQUEST;
    }
    public function getNote() {
        return $this->note;
    }
    public function getCreatedAt() {
        return $this->createdAt;
    }
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

}
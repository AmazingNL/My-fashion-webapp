<?php

class AppointmentSlot {
    private $slotId;
    private $startDate;
    private $endDate;
    private $isAvailable;
    private $createdAt;

    public function __construct($slotId, $startDate, $endDate, $isAvailable) {
        $this->slotId = $slotId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->isAvailable = $isAvailable;
        }

    public function getSlotId() {
        return $this->slotId;
    }

    public function getDate() {
        return $this->date;
    }

    public function getTime() {
        return $this->time;
    }

    public function getStatus() {
        return $this->status;
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
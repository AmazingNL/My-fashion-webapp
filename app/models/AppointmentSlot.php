<?php

namespace App\Models;

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



}
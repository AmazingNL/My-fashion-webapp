<?php

namespace App\Repositories;

use App\Models\AppointmentSlot;
use App\Models\Appointment;

interface IAppointmentSlotRepository {

    public function getAll(): array;
    public function findById($id): ?AppointmentSlot;
    public function findByAppointmentId($appointmentId): array;
    public function save(AppointmentSlot $appointmentSlot): void;
    public function update(AppointmentSlot $appointmentSlot): void;
    public function delete($id): void;

    // Define methods for appointment slot repository
}
<?php

namespace app\repositories;

use app\models\AppointmentSlot;
use app\models\Appointment;

interface IAppointmentSlotRepository {

    public function getAll(): array;
    public function findById($id): ?AppointmentSlot;
    public function findByAppointmentId($appointmentId): array;
    public function save(AppointmentSlot $appointmentSlot): void;
    public function update(AppointmentSlot $appointmentSlot): void;
    public function delete($id): void;

    // Define methods for appointment slot repository
}
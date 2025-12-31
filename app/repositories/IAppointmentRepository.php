<?php

namespace App\Repositories;

use App\Models\Appointment;

interface IAppointmentRepository {

    public function getAll(): array;
    public function findById($id): ?Appointment;
    public function findByUserId($userId): array;
    public function save(Appointment $appointment): void;
    public function update(Appointment $appointment): void;
    public function delete($id): void;

    // Define methods for appointment repository
}

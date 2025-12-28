<?php

namespace App\Repositories;

use App\Models\Appointment;
use App\Repositories\IAppointmentRepository;
use App\Core\RepositoryBase;

class AppointmentRepository extends RepositoryBase implements IAppointmentRepository {

    public function getAll(): array {
        $appointments = [];
        $results = $this->db->query("SELECT * FROM Appointments")->fetchAll();
        foreach ($results as $row) {
            $appointments[] = $this->mapToAppointment($row);
        }
        return $appointments;
    }

    public function findById($id): ?Appointment {
        $stmt = $this->db->prepare("SELECT * FROM Appointments WHERE appointmentId = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->mapToAppointment($row) : null;
    }

    public function findByUserId($userId): array {
        $appointments = [];
        $stmt = $this->db->prepare("SELECT * FROM Appointments WHERE userId = :userId");
        $stmt->execute(['userId' => $userId]);
        $results = $stmt->fetchAll();
        foreach ($results as $row) {
            $appointments[] = $this->mapToAppointment($row);
        }
        return $appointments;
    }
    public function save(Appointment $appointment): void {
        $stmt = $this->db->prepare("INSERT INTO Appointments (userId, date, time, description) VALUES (:userId, :date, :time, :description)");
        $stmt->execute([
            'userId' => $appointment->getUserId(),
            'date' => $appointment->getDate(),
            'time' => $appointment->getTime(),
            'description' => $appointment->getDescription()
        ]);
    }
    public function update(Appointment $appointment): void {
        $stmt = $this->db->prepare("UPDATE Appointments SET userId = :userId, date = :date, time = :time, description = :description WHERE appointmentId = :id");
        $stmt->execute([
            'id' => $appointment->getAppointmentId(),
            'userId' => $appointment->getUserId(),
            'date' => $appointment->getDate(),
            'time' => $appointment->getTime(),
            'description' => $appointment->getDescription()
        ]);
    }
    public function delete($id): void {
        $stmt = $this->db->prepare("DELETE FROM Appointments WHERE appointmentId = :id");
        $stmt->execute(['id' => $id]);
    }
    private function mapToAppointment($row): Appointment {
        return new Appointment(
            $row['appointmentId'],
            $row['userId'],
            $row['date'],
            $row['time'],
            $row['description']
            $row['createdAt'],
            $row['updatedAt']            
        );
    }
}
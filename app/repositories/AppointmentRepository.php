<?php

namespace App\Repositories;

use App\Core\RepositoryBase;
use App\Models\Appointment;
use App\Models\AppointmentStatus;
use PDO;

final class AppointmentRepository extends RepositoryBase implements IAppointmentRepository
{
    public function getAll(): array
    {
        return $this->getConnection()
            ->query("SELECT * FROM appointments ORDER BY appointmentId DESC")
            ->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function countByStatus(AppointmentStatus $status): int
    {
        $stmt = $this->getConnection()->prepare(
            "SELECT COUNT(*) FROM appointments WHERE status = :status"
        );
        $stmt->execute([':status' => $status->value]);
        return (int) $stmt->fetchColumn();
    }

    public function getAllWithSlot(): array
    {
        $sql = "
            SELECT 
                a.*,
                s.appointmentDate, s.startTime, s.endTime, s.isAvailable
            FROM appointments a
            INNER JOIN appointment_slots s ON s.slotId = a.slotId
            ORDER BY a.appointmentId DESC
        ";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findRawById(int $id): ?Appointment
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM appointments WHERE appointmentId = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToAppointment($row) : null;
    }

    public function findById(int $id): ?array
    {
        $sql = "
            SELECT 
                a.*,
                s.appointmentDate, s.startTime, s.endTime, s.isAvailable
            FROM appointments a
            INNER JOIN appointment_slots s ON s.slotId = a.slotId
            WHERE a.appointmentId = :id
            LIMIT 1
        ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findByUserId(int $userId): array
    {
        $sql = "
            SELECT 
                a.*,
                s.appointmentDate, s.startTime, s.endTime, s.isAvailable
            FROM appointments a
            INNER JOIN appointment_slots s ON s.slotId = a.slotId
            WHERE a.userId = :userId
            ORDER BY a.appointmentId DESC
        ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function create(Appointment $appointment): int
    {
        $sql = "
            INSERT INTO appointments (userId, slotId, designType, notes, status)
            VALUES (:userId, :slotId, :designType, :notes, :status)
        ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':userId' => $appointment->getUserId(),
            ':slotId' => $appointment->getSlotId(),
            ':designType' => $appointment->getDesignType(),
            ':notes' => $appointment->getNotes(),
            ':status' => $appointment->getStatus()->value,
        ]);

        return (int) $this->getConnection()->lastInsertId();
    }

    public function updateSlot(int $appointmentId, int $slotId): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE appointments 
            SET slotId = :slotId
            WHERE appointmentId = :id
        ");
        $stmt->execute([':slotId' => $slotId, ':id' => $appointmentId]);
    }

    public function updateDetails(int $appointmentId, ?string $designType, ?string $notes): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE appointments
            SET designType = :designType, notes = :notes
            WHERE appointmentId = :id
        ");
        $stmt->execute([
            ':designType' => $designType,
            ':notes' => $notes,
            ':id' => $appointmentId
        ]);
    }

    public function setStatus(int $appointmentId, AppointmentStatus $status): void
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE appointments 
            SET status = :status
            WHERE appointmentId = :id
        ");
        $stmt->execute([':status' => $status->value, ':id' => $appointmentId]);
    }

    public function delete(int $appointmentId): void
    {
        $stmt = $this->getConnection()->prepare("DELETE FROM appointments WHERE appointmentId = :id");
        $stmt->execute([':id' => $appointmentId]);
    }

    public function autoCancelPastAppointments(): int
    {
        // cancels if appointmentDate < today OR (today and endTime < now)
        $sql = "
            UPDATE appointments a
            INNER JOIN appointment_slots s ON s.slotId = a.slotId
            SET a.status = 'CANCELLED'
            WHERE a.status IN ('PENDING','CONFIRMED')
              AND (
                s.appointmentDate < CURDATE()
                OR (s.appointmentDate = CURDATE() AND s.endTime < CURTIME())
              )
        ";
        return $this->getConnection()->exec($sql) ?: 0;
    }

    private function mapToAppointment(array $row): Appointment
    {
        return new Appointment(
            (int) $row['appointmentId'],
            (int) $row['userId'],
            (int) $row['slotId'],
            $row['designType'] ?? null,
            $row['notes'] ?? null,
            AppointmentStatus::fromDb($row['status'] ?? null),
            $row['createdAt'] ?? null,
            $row['updatedAt'] ?? null
        );
    }
}

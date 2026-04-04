<?php

namespace App\Repositories;

use App\Core\RepositoryBase;
use App\Models\Appointment;
use App\Models\AppointmentStatus;
use PDO;
use RuntimeException;

final class AppointmentRepository extends RepositoryBase implements IAppointmentRepository
{
    public function getAll(): array
    {
        try {
        return $this->getConnection()
            ->query("SELECT * FROM appointments ORDER BY appointmentId DESC")
            ->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to load appointments: ' . $e->getMessage());
        }
    }

    public function countByStatus(AppointmentStatus $status): int
    {
        try {
        $stmt = $this->getConnection()->prepare(
            "SELECT COUNT(*) FROM appointments WHERE status = :status"
        );
        $stmt->execute([':status' => $status->value]);
        return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to count appointments by status: ' . $e->getMessage());
        }
    }

    public function getAllWithSlot(): array
    {
        try {
        $sql = "
            SELECT 
                a.*,
                s.appointmentDate, s.startTime, s.endTime, s.isAvailable
            FROM appointments a
            INNER JOIN appointment_slots s ON s.slotId = a.slotId
            ORDER BY a.appointmentId DESC
        ";
        return $this->getConnection()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to load appointments with slots: ' . $e->getMessage());
        }
    }


    public function findById(int $id): ?array
    {
        try {
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
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to find appointment: ' . $e->getMessage());
        }
    }

    public function findByUserId(int $userId): array
    {
        try {
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
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to find user appointments: ' . $e->getMessage());
        }
    }

    public function create(Appointment $appointment): int
    {
        try {
        $sql = "
            INSERT INTO appointments (userId, slotId, designType, notes, status)
            VALUES (:userId, :slotId, :designType, :notes, :status)
        ";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([
            ':userId' => $appointment->userId,
            ':slotId' => $appointment->slotId,
            ':designType' => $appointment->designType,
            ':notes' => $appointment->notes,
            ':status' => $appointment->status->value,
        ]);

        return (int) $this->getConnection()->lastInsertId();
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to create appointment: ' . $e->getMessage());
        }
    }

    public function updateSlot(int $appointmentId, int $slotId): bool
    {
        try {
        $stmt = $this->getConnection()->prepare("
            UPDATE appointments
            SET slotId = :slotId
            WHERE appointmentId = :id
        ");
        $stmt->execute([':slotId' => $slotId, ':id' => $appointmentId]);
        return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to update appointment slot: ' . $e->getMessage());
        }
    }

    public function updateDetails(int $appointmentId, ?string $designType, ?string $notes): bool
    {
        try {
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
        return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to update appointment details: ' . $e->getMessage());
        }
    }

    public function setStatus(int $appointmentId, AppointmentStatus $status): bool
    {
        try {
        $stmt = $this->getConnection()->prepare("
            UPDATE appointments
            SET status = :status
            WHERE appointmentId = :id
        ");
        $stmt->execute([':status' => $status->value, ':id' => $appointmentId]);
        return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to set appointment status: ' . $e->getMessage());
        }
    }

    public function delete(int $appointmentId): bool
    {
        try {
        $stmt = $this->getConnection()->prepare("DELETE FROM appointments WHERE appointmentId = :id");
        $stmt->execute([':id' => $appointmentId]);
        return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to delete appointment: ' . $e->getMessage());
        }
    }

    public function autoCancelPastAppointments(): int
    {
        try {
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
        } catch (\Throwable $e) {
            throw new RuntimeException('Failed to auto-cancel past appointments: ' . $e->getMessage());
        }
    }

}

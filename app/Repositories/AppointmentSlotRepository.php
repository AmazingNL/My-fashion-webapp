<?php

namespace App\Repositories;

use App\Core\RepositoryBase;
use App\Models\AppointmentSlot;
use PDO;

final class AppointmentSlotRepository extends RepositoryBase implements IAppointmentSlotRepository
{
    public function getAll(): array
    {
        $stmt = $this->getConnection()->query("
            SELECT * FROM appointment_slots
            ORDER BY appointmentDate ASC, startTime ASC
        ");
        return $this->mapMany($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function findById(int $slotId): ?AppointmentSlot
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM appointment_slots WHERE slotId = :id");
        $stmt->execute([':id' => $slotId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->mapOne($row) : null;
    }

    public function findByDate(string $date): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM appointment_slots
            WHERE appointmentDate = :d
            ORDER BY startTime ASC
        ");
        $stmt->execute([':d' => $date]);
        return $this->mapMany($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function findAvailableByDate(string $date): array
    {
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM appointment_slots
            WHERE appointmentDate = :d
              AND isAvailable = 1
              AND (appointmentDate > CURDATE() OR (appointmentDate = CURDATE() AND endTime > CURTIME()))
            ORDER BY startTime ASC
        ");
        $stmt->execute([':d' => $date]);
        return $this->mapMany($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function create(AppointmentSlot $slot): int
    {
        $stmt = $this->getConnection()->prepare("
            INSERT INTO appointment_slots (appointmentDate, startTime, endTime, isAvailable)
            VALUES (:d, :s, :e, :a)
        ");
        $stmt->execute([
                ':d' => $slot->appointmentDate,
                ':s' => $slot->startTime,
                ':e' => $slot->endTime,
                ':a' => $slot->isAvailable ? 1 : 0,
        ]);

        return (int)$this->getConnection()->lastInsertId();
    }

    public function setAvailability(int $slotId, bool $available): bool
    {
        $stmt = $this->getConnection()->prepare("
            UPDATE appointment_slots
            SET isAvailable = :a
            WHERE slotId = :id
        ");
            $stmt->execute([':a' => $available ? 1 : 0, ':id' => $slotId]);
            return $stmt->rowCount() > 0;
    }

    public function lockAvailableSlotForUpdate(int $slotId): ?array
    {
        // must be called inside a transaction
        $stmt = $this->getConnection()->prepare("
            SELECT * FROM appointment_slots
            WHERE slotId = :id AND isAvailable = 1
            FOR UPDATE
        ");
        $stmt->execute([':id' => $slotId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function mapOne(array $row): AppointmentSlot
    {
        return new AppointmentSlot(
            (int)$row['slotId'],
            (string)$row['appointmentDate'],
            (string)$row['startTime'],
            (string)$row['endTime'],
            (bool)$row['isAvailable'],
            $row['createdAt'] ?? null
        );
    }

    private function mapMany(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) $out[] = $this->mapOne($r);
        return $out;
    }
}

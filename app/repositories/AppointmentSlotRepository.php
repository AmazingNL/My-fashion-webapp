<?php

namespace app\repositories;

use app\models\AppointmentSlot;
use app\models\Appointment;
use app\repositories\IAppointmentSlotRepository;
use app\core\RepositoryBase;

class AppointmentSlotRepository extends RepositoryBase implements IAppointmentSlotRepository {

    public function getAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM AppointmentSlots");
        $stmt->execute();
        $results = $stmt->fetchAll();
        $appointmentSlots = [];
        foreach ($results as $row) {
            $appointmentSlots[] = new AppointmentSlot(
                $row['id'],
                $row['appointmentId'],
                $row['startTime'],
                $row['endTime'],
                $row['status']
            );
        }
        return $appointmentSlots;
    }

    public function findById($id): ?AppointmentSlot {
        $stmt = $this->db->prepare("SELECT * FROM AppointmentSlots WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row) {
            return new AppointmentSlot(
                $row['id'],
                $row['appointmentId'],
                $row['startTime'],
                $row['endTime'],
                $row['status']
            );
        }
        return null;
    }
    public function findByAppointmentId($appointmentId): array {
        $stmt = $this->db->prepare("SELECT * FROM AppointmentSlots WHERE appointmentId = :appointmentId");
        $stmt->bindParam(':appointmentId', $appointmentId);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $appointmentSlots = [];
        foreach ($results as $row) {
            $appointmentSlots[] = new AppointmentSlot(
                $row['id'],
                $row['appointmentId'],
                $row['startTime'],
                $row['endTime'],
                $row['status']
            );
        }
        return $appointmentSlots;
    }
    public function save(AppointmentSlot $appointmentSlot): void {
        $stmt = $this->db->prepare("INSERT INTO appointmentSlots (appointmentId, startTime, endTime, status) 
                                    VALUES (:appointmentId, :startTime, :endTime, :status)");
        $stmt->bindParam(':appointmentId', $appointmentSlot->getAppointmentId());
        $stmt->bindParam(':startTime', $appointmentSlot->getStartTime());
        $stmt->bindParam(':endTime', $appointmentSlot->getEndTime());
        $stmt->bindParam(':status', $appointmentSlot->getStatus());
        $stmt->execute();
    }
    public function update(AppointmentSlot $appointmentSlot): void {
        $stmt = $this->db->prepare("UPDATE appointmentSlots SET appointmentId = :appointmentId, startTime = :startTime, 
                                    endTime = :endTime, status = :status WHERE id = :id");
        $stmt->bindParam(':appointmentId', $appointmentSlot->getAppointmentId());
        $stmt->bindParam(':startTime', $appointmentSlot->getStartTime());
        $stmt->bindParam(':endTime', $appointmentSlot->getEndTime());
        $stmt->bindParam(':status', $appointmentSlot->getStatus());
        $stmt->bindParam(':id', $appointmentSlot->getId());
        $stmt->execute();
    }
    public function delete($id): void {
        $stmt = $this->db->prepare("DELETE FROM appointmentSlots WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
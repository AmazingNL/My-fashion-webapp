<?php

declare(strict_types=1);

namespace App\Models;

class ActivityLog
{
    private ?int $logId;
    private ?int $userId;
    private string $action;
    private ?string $entityType;
    private ?int $entityId;
    private ?string $details;
    private ?string $ipAddress;
    private ?string $userAgent;
    private ?string $createdAt;

    public function __construct( ?int $logId,
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $details = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $createdAt = null
    ) {
        $this->logId = $logId;
        $this->userId = $userId;
        $this->action = $action;
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->details = $details;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getLogId(): ?int { return $this->logId; }
    public function getUserId(): ?int { return $this->userId; }
    public function getAction(): string { return $this->action; }
    public function getEntityType(): ?string { return $this->entityType; }
    public function getEntityId(): ?int { return $this->entityId; }
    public function getDetails(): ?string { return $this->details; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function getUserAgent(): ?string { return $this->userAgent; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    // Setters
    public function setLogId(?int $logId): void { $this->logId = $logId; }
}
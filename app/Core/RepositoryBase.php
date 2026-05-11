<?php

namespace App\Core;

use App\Config;
use PDO;

class RepositoryBase
{
    protected PDO $connection;

    /** @var PDO|null */
    private static ?PDO $sharedConnection = null;

    public function __construct()
    {
        if (self::$sharedConnection === null) {
            $config = new Config();

            self::$sharedConnection = new PDO(
                'mysql:host=' . $config::dbServerName() .
                ';dbname=' . $config::dbName() .
                ';charset=utf8mb4',
                $config::dbUsername(),
                $config::dbPassword()
            );

            self::$sharedConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$sharedConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        $this->connection = self::$sharedConnection;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /* ============================
        TRANSACTION HELPERS
       ============================ */

    public function beginTransaction(): void
    {
        if (!$this->connection->inTransaction()) {
            $this->connection->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->connection->inTransaction()) {
            $this->connection->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
}

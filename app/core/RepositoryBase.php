<?php

namespace App\core;

use app\config;
use PDO;

class RepositoryBase {
    // Repository code here

    protected PDO $connection;

    public function __construct() {
        
        $config = new Config();

        $this -> connection = new PDO(
            'mysql:host='. $config::DB_SERVER_NAME 
            . ';dbname=' . $config::DB_NAME . ';charset=utf8mb4';
        ) 

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
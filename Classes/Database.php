<?php

namespace Classes;

use Dotenv\Dotenv;
use PDO;

require __DIR__ . '/../vendor/autoload.php';
Dotenv::createUnsafeImmutable(__DIR__ . '/../')->load();

class Database
{
    private PDO $dbConnection;

    public function __construct()
    {
        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $dbUsername = $_ENV['DB_USERNAME'];
        $dbPassword = $_ENV['DB_PASSWORD'];

        $this->dbConnection = new PDO("pgsql:dbname=$dbName;host=$dbHost;port=5433", $dbUsername, $dbPassword);
    }

    /**
     * @return PDO
     */
    public function getDbConnection(): PDO
    {
        return $this->dbConnection;
    }
}
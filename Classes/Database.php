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
        $dbPort = $_ENV['DB_PORT'];
        $dbUsername = $_ENV['DB_USERNAME'];
        $dbPassword = $_ENV['DB_PASSWORD'];

        $this->dbConnection = new PDO("pgsql:dbname=$dbName;host=$dbHost;port=$dbPort", $dbUsername, $dbPassword);
    }


    public function getDbConnection(): PDO
    {
        return $this->dbConnection;
    }
}
<?php

namespace Classes;

use PDO;

class Auth
{
    public static function authenticate(PDO $connection, int $id, string $password): bool
    {
        $sql = "SELECT password FROM users  WHERE id=:id";

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultPassword = $stmt->fetchColumn();

        return password_verify($password, $resultPassword);
    }

    public static function login(): void
    {
        session_regenerate_id(true);

        $_SESSION['is_logged_in'] = true;
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
    }
}
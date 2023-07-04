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

    public static function isConfirmed(PDO $connection, int $id): bool
    {
        $sql = "SELECT is_confirmed FROM users WHERE id=:id";

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public static function login(PDO $connection, int $id): void
    {
        session_regenerate_id(true);

        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_id'] = $id;

        self::setUserType($connection, $id);
    }

    private
    static function setUserType(
        PDO $connection,
        int $id
    ): void {
        $sql = "SELECT user_types.name FROM users
            JOIN user_types ON users.type = user_types.type_id
            WHERE users.id = :id";

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $userType = $stmt->fetchColumn();

        $_SESSION['user_type'] = $userType;
    }

    public
    static function isAuthenticated(): bool
    {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
    }

    public
    static function checkUserType(
        string $type
    ): bool {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === $type;
    }
}
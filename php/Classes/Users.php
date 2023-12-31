<?php

namespace php\Classes;

use PDO;

class Users
{
    public static function create(PDO $connection, array $options): void
    {
        if (!array_key_exists("ip", $options)) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $options["ip"] = $ip;
        }
        $options["password"] = password_hash($options["password"], PASSWORD_DEFAULT);

        $columnsString = implode(', ', array_keys($options));
        $valuesString = implode(', :', array_keys($options));

        $sql = "INSERT INTO users ($columnsString) VALUES (:$valuesString)";

        $stmt = $connection->prepare($sql);

        foreach ($options as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }

    public static function getWithJoinUserType(
        PDO $connection,
        array $columnsSELECT = [],
        array $optionsWHERE = []
    ): array {
        $columnsString = !empty($columnsSELECT) ? implode(', ', $columnsSELECT) : 'users.*';

        if (!empty($optionsWHERE)) {
            $whereClause = implode(
                'AND ',
                array_map(static fn($column) => $column . ' = :' . $column, array_keys($optionsWHERE))
            );
            $sql = "SELECT $columnsString, user_types.readable_name FROM users
            JOIN user_types ON user_types.type_id = users.type WHERE ($whereClause) and user_types.type_id <> 1";
            $stmt = $connection->prepare($sql);

            foreach ($optionsWHERE as $column => $value) {
                $stmt->bindValue(':' . $column, $value);
            }
        } else {
            $sql = "SELECT $columnsString, user_types.readable_name FROM users
            JOIN user_types ON user_types.type_id = users.type WHERE user_types.type_id <> 1";
            $stmt = $connection->prepare($sql);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function update(PDO $connection, array $optionsSET = [], array $optionsWHERE = []): void
    {
        $setClause = implode(
            ', ',
            array_map(static fn($column) => $column . ' = :' . $column, array_keys($optionsSET))
        );
        $whereClause = implode(
            'AND ',
            array_map(static fn($column) => $column . ' = :' . $column, array_keys($optionsWHERE))
        );

        $sql = "UPDATE users SET $setClause WHERE ($whereClause)";
        $stmt = $connection->prepare($sql);

        foreach ($optionsSET as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        foreach ($optionsWHERE as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }

    public static function delete(PDO $connection, array $optionsWHERE = []): void
    {
        $whereClause = implode(
            'AND ',
            array_map(static fn($column) => $column . ' = :' . $column, array_keys($optionsWHERE))
        );

        $sql = "DELETE FROM users WHERE ($whereClause)";
        $stmt = $connection->prepare($sql);

        foreach ($optionsWHERE as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }

    public static function deleteUnconfirmedUsers(PDO $connection): void
    {
        self::delete($connection, ['is_confirmed' => 0]);
    }
}

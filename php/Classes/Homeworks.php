<?php

namespace php\Classes;

use PDO;

class Homeworks
{
    public static function create(PDO $connection, array $options): int
    {
        $columnsString = implode(', ', array_keys($options));
        $valuesString = implode(', :', array_keys($options));

        $sql = "INSERT INTO homeworks ($columnsString) VALUES (:$valuesString)";
        $stmt = $connection->prepare($sql);

        foreach ($options as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();

        return (int)$connection->query("SELECT last_value FROM homeworks_id_seq")->fetchColumn();
    }

    public static function attachHomeworkToCourse(
        PDO $connection,
        int $homework_id,
        int $course_id
    ): void {
        $sql = "INSERT INTO course_homeworks (course_id, homework_id) VALUES (:course_id, :homework_id)";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':course_id', $course_id);
        $stmt->bindValue(':homework_id', $homework_id);

        $stmt->execute();
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

        $sql = "UPDATE homeworks SET $setClause WHERE ($whereClause)";
        $stmt = $connection->prepare($sql);

        foreach ($optionsSET as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        foreach ($optionsWHERE as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }

    public static function getAttachedHomeworks(PDO $connection, int $id): array
    {
        $sql = "SELECT * FROM course_homeworks
        JOIN homeworks ON homeworks.id = course_homeworks.homework_id WHERE course_homeworks.course_id = :id";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':id', $id);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get(
        PDO $connection,
        array $columnsSELECT = [],
        array $optionsWHERE = []
    ): array {
        $columnsString = !empty($columnsSELECT) ? implode(', ', $columnsSELECT) : '*';

        if (!empty($optionsWHERE)) {
            $whereClause = implode(
                'AND ',
                array_map(static fn($column) => $column . ' = :' . $column, array_keys($optionsWHERE))
            );
            $sql = "SELECT $columnsString FROM homeworks WHERE ($whereClause)";
            $stmt = $connection->prepare($sql);

            foreach ($optionsWHERE as $column => $value) {
                $stmt->bindValue(':' . $column, $value);
            }
        } else {
            $sql = "SELECT $columnsString FROM homeworks";
            $stmt = $connection->prepare($sql);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function delete(PDO $connection, array $optionsWHERE = []): void
    {
        $whereClause = implode(
            'AND ',
            array_map(static fn($column) => $column . ' = :' . $column, array_keys($optionsWHERE))
        );

        $sql = "DELETE FROM homeworks WHERE ($whereClause)";
        $stmt = $connection->prepare($sql);

        foreach ($optionsWHERE as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }
}

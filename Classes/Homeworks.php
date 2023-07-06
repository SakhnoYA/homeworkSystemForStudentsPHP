<?php

namespace Classes;

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
        echo $sql;
        echo "<pre>";
        print_r($options);
        echo "</pre>";
        return (int)$connection->query("SELECT last_value FROM homework_id_seq")->fetchColumn();
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
        echo $sql;
        echo "<pre>";
        print_r($optionsSET);
        echo "</pre>";
        $stmt->execute();
    }
}
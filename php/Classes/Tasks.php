<?php

namespace php\Classes;

use PDO;

class Tasks
{
    public static function create(PDO $connection, array $options): int
    {
        $columnsString = implode(', ', array_keys($options));
        $valuesString = implode(', :', array_keys($options));

        $sql = "INSERT INTO tasks ($columnsString) VALUES (:$valuesString)";
        $stmt = $connection->prepare($sql);

        foreach ($options as $column => $value) {
            if (is_array($value)) {
                $value = '{' . implode(
                    ', ',
                    preg_split('/\s+/', trim($value[0]))
                ) . '}';
                $stmt->bindValue(':' . $column, $value);
            } else {
                $stmt->bindValue(':' . $column, preg_replace('/\s+/', ' ', trim($value)));
            }
        }

        $stmt->execute();

        return (int)$connection->query("SELECT last_value FROM tasks_id_seq")->fetchColumn();
    }

    public static function getByIds(PDO $connection, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(', ', array_map(static fn($id) => ":id_$id", $ids));

        $sql = "SELECT * FROM tasks WHERE id IN ($placeholders)";

        $stmt = $connection->prepare($sql);

        foreach ($ids as $id) {
            $stmt->bindValue(":id_$id", $id);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAttachedTasks(PDO $connection, int $id): array
    {
        $sql = "SELECT * FROM homework_tasks
        JOIN tasks t ON t.id = homework_tasks.task_id WHERE homework_tasks.homework_id = :id";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':id', $id);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function attachTaskToHomework(
        PDO $connection,
        int $task_id,
        int $homework_id,
    ): void {
        $sql = "INSERT INTO homework_tasks (homework_id, task_id) VALUES (:homework_id, :task_id)";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':task_id', $task_id);
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

        $sql = "UPDATE tasks SET $setClause WHERE ($whereClause)";
        $stmt = $connection->prepare($sql);

        foreach ($optionsSET as $column => $value) {
            if (is_array($value)) {
                $value = '{' . implode(
                    ', ',
                    preg_split('/\s+/', trim($value[0]))
                ) . '}';
                $stmt->bindValue(':' . $column, $value);
            } else {
                $stmt->bindValue(':' . $column, preg_replace('/\s+/', ' ', trim($value)));
            }
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

        $sql = "DELETE FROM tasks WHERE ($whereClause)";
        $stmt = $connection->prepare($sql);

        foreach ($optionsWHERE as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }
}

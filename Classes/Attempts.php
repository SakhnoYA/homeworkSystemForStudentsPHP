<?php

namespace Classes;

use PDO;

class Attempts
{
    public static function create(PDO $connection): int
    {
        $sql = "INSERT INTO attempts (score)VALUES (DEFAULT)";

        $connection->query($sql);

        return (int)$connection->query("SELECT last_value FROM attempts_id_seq")->fetchColumn();
    }

    public static function createContent(PDO $connection, int $attempt_id, int $task_id, ?array $user_input): void
    {
        $sql = "INSERT INTO attempt_inputs (attempt_id, task_id, user_input)VALUES (:attempt_id, :task_id, :user_input)";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':attempt_id', $attempt_id);
        $stmt->bindValue(':task_id', $task_id);

        if (!empty($user_input)) {
            $user_input_value = '{' . implode(', ', preg_split('/\s+/', trim($user_input[0]))) . '}';
        } else {
            $user_input_value = '{}';
        }

        $stmt->bindValue(':user_input', $user_input_value);

        $stmt->execute();
    }

    public static function attachAttemptToHomeworkUser(
        PDO $connection,
        int $user_id,
        int $homework_id,
        int $attempt_id
    ): void {
        $sql = "INSERT INTO user_homework_attempts (user_id, homework_id, attempt_id)VALUES (:user_id, :homework_id, :attempt_id)";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':attempt_id', $attempt_id);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':homework_id', $homework_id);

        $stmt->execute();
    }
}
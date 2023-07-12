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
        $sql = "INSERT INTO attempt_inputs (attempt_id, task_id, user_input)
                                VALUES (:attempt_id, :task_id, :user_input)";

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
        $sql = "INSERT INTO user_homework_attempts (user_id, homework_id, attempt_id)
                                            VALUES (:user_id, :homework_id, :attempt_id)";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':attempt_id', $attempt_id);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':homework_id', $homework_id);

        $stmt->execute();
    }

    public static function getCount(
        PDO $connection,
        int $user_id,
        int $homework_id
    ): int {
        $sql = "SELECT COUNT(attempt_id) FROM user_homework_attempts
                WHERE user_id = :user_id AND homework_id = :homework_id";

        $stmt = $connection->prepare($sql);

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':homework_id', $homework_id);

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public static function getContent(PDO $connection, int $attempt_id): array
    {
        $sql = "SELECT * FROM attempt_inputs AS ai
                JOIN tasks AS t ON ai.task_id = t.id WHERE ai.attempt_id = :attempt_id";

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':attempt_id', $attempt_id);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByHomework(PDO $connection, int $homework_id): array
    {
        $sql = "SELECT user_id,
          CASE
            WHEN ROW_NUMBER() OVER (ORDER BY attempt_id) = 1 THEN 'первая'
            WHEN ROW_NUMBER() OVER (ORDER BY attempt_id) = 2 THEN 'вторая'
            WHEN ROW_NUMBER() OVER (ORDER BY attempt_id) = 3 THEN 'третья'
            ELSE 'другая'
          END AS attempt_number,
          score, submission_time, attempt_id
        FROM user_homework_attempts 
        JOIN attempts  ON attempt_id = id
        WHERE homework_id = :homework_id";

        $stmt = $connection->prepare($sql);

        $stmt->bindParam(':homework_id', $homework_id);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

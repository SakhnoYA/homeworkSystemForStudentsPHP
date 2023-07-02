<?php

namespace Classes;

use PDO;

class Courses
{
    public static function create(PDO $connection, array $options): void
    {
        $columnsString = implode(', ', array_keys($options));
        $valuesString = implode(', :', array_keys($options));

        $sql = "INSERT INTO courses ($columnsString) VALUES (:$valuesString)";
        $stmt = $connection->prepare($sql);

        foreach ($options as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }
        $stmt->execute();
    }

    public static function attachCourseToUser(PDO $connection, int $id_user, int $course_id): void
    {
        $sql = "INSERT INTO user_courses (user_id, course_id) VALUES (:user_id, :course_id)";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':user_id', $id_user);
        $stmt->bindValue(':course_id', $course_id);

        $stmt->execute();
    }

    public static function detachCourseFromUser(PDO $connection, int $id_user, int $course_id): void
    {
        $sql = "DELETE FROM user_courses WHERE user_id = :user_id AND course_id = :course_id";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':user_id', $id_user);
        $stmt->bindValue(':course_id', $course_id);

        $stmt->execute();
    }

    public static function getAttachedCourses(PDO $connection, int $id): array
    {
        $sql = "SELECT courses.title FROM user_courses JOIN courses ON courses.id = user_courses.course_id  WHERE user_courses.user_id = :id";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':id', $id);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnattachedCourses(PDO $connection, int $user_id): array
    {
        $sql = "SELECT courses.title
            FROM courses
            WHERE courses.id NOT IN (
                SELECT course_id
                FROM user_courses
                WHERE user_id = :user_id
            )";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
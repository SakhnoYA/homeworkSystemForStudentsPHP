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

        $sql = "UPDATE courses SET $setClause WHERE ($whereClause)";
        $stmt = $connection->prepare($sql);

        foreach ($optionsSET as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        foreach ($optionsWHERE as $column => $value) {
            $stmt->bindValue(':' . $column, $value);
        }

        $stmt->execute();
    }

    public static function attachCourseToUser(
        PDO $connection,
        int $user_id,
        int $course_id,
        bool $is_confirmed = false
    ): void {
        $sql = "INSERT INTO user_courses (user_id, course_id, is_confirmed) VALUES (:user_id, :course_id, :is_confirmed)";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':course_id', $course_id);
        $stmt->bindValue(':is_confirmed', $is_confirmed, PDO::PARAM_BOOL);

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
            $sql = "SELECT $columnsString FROM courses WHERE ($whereClause)";
            $stmt = $connection->prepare($sql);

            foreach ($optionsWHERE as $column => $value) {
                $stmt->bindValue(':' . $column, $value);
            }
        } else {
            $sql = "SELECT $columnsString FROM courses";
            $stmt = $connection->prepare($sql);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function getAttachedCourses(PDO $connection, int $id, ?bool $is_confirmed = null): array
    {
        $sql = "SELECT * FROM user_courses JOIN courses ON courses.id = user_courses.course_id WHERE user_courses.user_id = :id";

        if ($is_confirmed !== null) {
            $sql .= " AND user_courses.is_confirmed = :is_confirmed";
        }

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':id', $id);

        if ($is_confirmed !== null) {
            $stmt->bindValue(':is_confirmed', $is_confirmed);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public
    static function getUnattachedCourses(
        PDO $connection,
        int $user_id
    ): array {
        $sql = "SELECT courses.id, courses.title
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

    public
    static function getUnconfirmedUserCourseRelationships(
        PDO $connection
    ): array {
        $sql = "SELECT uc.user_id, uc.course_id,  first_name, last_name, middle_name, title, readable_name FROM user_courses uc JOIN courses c ON c.id = uc.course_id JOIN users u ON u.id = uc.user_id JOIN user_types ut ON ut.type_id = u.type WHERE uc.is_confirmed = FALSE";

        return $connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public
    static function confirmUserCourseRelationship(
        PDO $connection,
        int $user_id,
        int $course_id
    ): void {
        $sql = "UPDATE user_courses SET is_confirmed = TRUE WHERE user_id = :user_id AND course_id = :course_id";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':course_id', $course_id);

        $stmt->execute();
    }

    public
    static function deleteUserCourseRelationship(
        PDO $connection,
        int $user_id,
        int $course_id
    ): void {
        $sql = "DELETE FROM user_courses WHERE user_id = :user_id AND course_id = :course_id";

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':user_id', $user_id);
        $stmt->bindValue(':course_id', $course_id);

        $stmt->execute();
    }
}
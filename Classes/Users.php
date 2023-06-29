<?php

namespace Classes;

use PDO;

class Users
{
//create(ассоциативный массив) - одного пользователь создает
//update(ассоциативный массив)
//get(ассоциативный массив)
//delete(ассоциативный массив)
//attachCourseToUser(id_user, course_id)
//getUserCourseList(id)
//deleteUnconfirmedUsers()

    public static function create($connection, $options = []): string
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

        $sql = "INSERT INTO users (" . implode(', ', array_keys($options)) . ")
        VALUES (:" . implode(', :', array_keys($options)) . ")";

        $stmt = $connection->prepare($sql);


        foreach ($options as $column => $value) {
            $stmt->bindValue(':' . $column, $value, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $connection->lastInsertId();
//        $sql = "INSERT INTO users (id, registration_date, first_name, last_name, middle_name, password, type, ip, is_confirmed)
//                VALUES (1, '2023-06-18', 'John', 'Doe', 'Smith', 'password123', 1, '127.0.0.1', true);";
//        id                smallint                     not null
////    registration_date date    default CURRENT_DATE not null,
//    first_name        varchar(30)                  not null,
//    last_name         varchar(30)                  not null,
//    middle_name       varchar(30),
//    password          varchar(255)                 not null,
//    type              integer                      not null
//        references user_types,
//    ip                varchar(45),
////    is_confirmed      boolean default false        not null
    }
}
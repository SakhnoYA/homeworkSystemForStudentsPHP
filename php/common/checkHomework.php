<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/Classes/Autoloader.php';

use php\Classes\Attempts;
use php\Classes\Autoloader;
use php\Classes\Database;
use php\Classes\Session;

Autoloader::register();
Session::start();

try {
    $connection = (new Database())->getDbConnection();
    $lastAttemptId = Attempts::create($connection);

    foreach ($_POST as $task) {
        Attempts::createContent($connection, $lastAttemptId, $task['id'], $task['user_input'] ?? null);
    }
    Attempts::attachAttemptToHomeworkUser($connection, $_SESSION['user_id'], $_GET['homework_id'], $lastAttemptId);

    $response = ['attempt_id' => urlencode(base64_encode($lastAttemptId))];
    header('Content-Type: application/json');
    echo json_encode($response, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    die("Произошла ошибка: " . $e->getMessage());
}

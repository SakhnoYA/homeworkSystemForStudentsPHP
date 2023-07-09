<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Autoloader;
use Classes\Database;
use Classes\Attempts;
use Classes\Session;

Autoloader::register();
Session::start();

$connection = (new Database())->getDbConnection();
$lastAttemptId = Attempts::create($connection);

foreach ($_POST as $task) {
    Attempts::createContent($connection, $lastAttemptId, $task['id'], $task['user_input'] ?? null);
}
Attempts::attachAttemptToHomeworkUser($connection, $_SESSION['user_id'], $_GET['homework_id'], $lastAttemptId);


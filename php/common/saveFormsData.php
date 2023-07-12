<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/Classes/Autoloader.php';

use php\Classes\Autoloader;
use php\Classes\Session;

Autoloader::register();
Session::start();

foreach ($_POST as $key => $value) {
    $_SESSION[$key] = $value;
}

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Autoloader;
use Classes\Session;

Autoloader::register();
Session::start();

foreach ($_POST as $key => $value) {
    $_SESSION[$key] = $value;
}

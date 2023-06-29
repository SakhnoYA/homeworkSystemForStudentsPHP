<?php

namespace Classes;

class Autoloader
{
    public static function register() {
        spl_autoload_register(static function ($class) {
            $class = str_replace('\\', '/', $class);
            require_once dirname(__DIR__) . "/$class.php";
        });
    }
}
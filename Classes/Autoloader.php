<?php

namespace Classes;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(static function ($class) {
            $class = str_replace('\\', '/', $class);
            require_once dirname(__DIR__) . "/$class.php";
        });
    }
}

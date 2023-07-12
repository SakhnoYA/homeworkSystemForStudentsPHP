<?php

namespace php\Classes;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(static function ($class) {
//            echo dirname($_SERVER['DOCUMENT_ROOT']);
//            echo dirname($_SERVER['DOCUMENT_ROOT']) . "/$class.php";
            $class = str_replace('\\', '/', $class);
//            echo $class;
            require_once $_SERVER['DOCUMENT_ROOT'] . "/$class.php";
        });
    }
}

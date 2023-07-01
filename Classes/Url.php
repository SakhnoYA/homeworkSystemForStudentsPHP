<?php
/** @noinspection PhpNoReturnAttributeCanBeAddedInspection */

namespace Classes;

class Url
{
    public static function redirect(string $path): void
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        header("Location: $protocol://" . $_SERVER['HTTP_HOST'] . $path);
        exit;
    }

}
<?php
/** @noinspection PhpNoReturnAttributeCanBeAddedInspection */

namespace Classes;

class Url
{
    public static function redirect(string $path, int $seconds = 0): void
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        $url = "$protocol://" . $_SERVER['HTTP_HOST'] . "/" . $path;

        if ($seconds > 0) {
            header("refresh:$seconds;url=$url");
        } else {
            header("Location: $url");
        }
        exit;
    }

}
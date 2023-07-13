<?php

namespace php\Classes;

class Url
{
    public static function redirect(string $path, string $queryString = ''): void
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        $url = "$protocol://" . $_SERVER['HTTP_HOST'] . "/" . $path;

        if (!empty($queryString)) {
            $url .= '?' . $queryString;
        }

        header("Location: $url");

        exit;
    }
}

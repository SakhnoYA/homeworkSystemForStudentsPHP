<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Auth;
use Classes\Autoloader;
use Classes\Courses;
use Classes\Database;
use Classes\Session;
use Classes\Url;
use Classes\Users;

Autoloader::register();
Session::start();

if (!Auth::checkUserType('admin')) {
    Url::redirect('forbidden.php');
}

$user = null;
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }
//        if (isset($_POST['title'])) {
//            Courses::create($connection, $_POST);
//            Url::redirect(substr($_SERVER['PHP_SELF'], 1));
//        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            $connection = (new Database())->getDbConnection();
            $user = Users::get($connection, ['first_name', 'middle_name', 'last_name', 'type'], ['id' => $_GET['id']]);
        }
    }
} catch (PDOException $e) {
    $error = "Произошла ошибка базы данных: " . $e->getCode();
}


?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Домашние задания - Homework System</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
    <link rel="manifest" href="/icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="icons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="header__content">
        <img src="/images/icon.png" class="header-logo" alt="Homework System logo">
        <ul class="tabs">
            <li>
                <a href="main.php" class="tabs-tab">Пользователи</a>
            </li>
            <li>
                <a href="registrations.php" class="tabs-tab">Регистрации</a>
            </li>
            <li>
                <a href="" class="tabs-tab tabs-tab_active">Создание курса</a>
            </li>
            <li>
                <a href="" class="tabs-tab">Запросы доступа</a>
            </li>
        </ul>
        <form method="post">
            <button type="submit" name="logout" class="header__button-login">Выйти</button>
        </form>
    </div>
</header>
<main class="dark-grey-background">
    <div class="main__content">
        <div class="login__modal mt6rem">
            <div class="login__header">Создать курс</div>
            <p class="login__form-input"> <?= $user['first_name'] . $user['middle_name'] . " " . $user['last_name'] . " " . $user['type'] ?></p>
            <div class="role"><?= $user['type'] ?></div>
            <form method="post">
                <label for="attach_course">Прикрепить к курсу:</label>
                <select multiple id="attach_course">
                    <!-- Варианты курсов -->
                </select>

                <label for="detach_course">Открепить от курса:</label>
                <select multiple id="detach_course">
                    <!-- Варианты курсов -->
                </select>

                <button type="submit">Отправить</button>
            </form>
            <?php
            if (!empty($error)) : ?>
                <p class="errorMessage"><?= $error ?></p>
            <?php
            endif; ?>
        </div>
    </div>
</main>
<footer>
    <div class="footer">
        <div class="footer-copyright">© 2023 Ярослав Сахно</div>
        <a class="github_link" href="https://github.com/SakhnoYA">Мой гитхаб</a>
    </div>
</footer>

</body>
</html>
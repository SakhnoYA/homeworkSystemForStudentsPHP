<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Attempts;
use Classes\Auth;
use Classes\Autoloader;
use Classes\Database;
use Classes\Session;
use Classes\Url;

Autoloader::register();
Session::start();

if (!(Auth::checkUserType('student') || Auth::checkUserType('teacher'))) {
    Url::redirect('basic/forbidden.php');
}

$connection = (new Database())->getDbConnection();
//try {
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        Session::destroySession();
        Url::redirect('index.php');
    }
}

if (isset($_GET['attempt_id'])) {
    $attemptContent = Attempts::getContent($connection, base64_decode(urldecode($_GET['attempt_id'])));
} else {
    die("No Id in query parameter");
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
                <a href="<?= $_SESSION['user_type'] === 'teacher' ? '/teacher/main.php' : '/student/main.php' ?>"
                   class="
                   tabs-tab">Курсы</a>
            </li>
            <li>
                <a class="tabs-tab ">Запросить доступ</a>
            </li>
        </ul>
        <form method="post">
            <button type="submit" name="logout" class="header__button-login">Выйти</button>
        </form>
    </div>
</header>
<main class="dark-grey-background">
    <div class="main__content mt4rem">
        <?php
        foreach ($attemptContent as $task): ?>
            <div class="login__modal w40p  mb1rem  <?= $task['is_correct'] ? 'correct' : 'wrong' ?> ">
                <div class="login__header"><?= $task['title'] ?></div>
                <p><?= $task['description'] ?></p>
                <div class="role">Ответ студента: <?= $task['user_input'] ?></div>
                <div class="role">Правильный ответ: <?= $task['answer'] ?></div>
                <div class="role">Количество баллов: <?= $task['max_score'] ?></div>
            </div>
        <?php
        endforeach; ?>
        <div class="register__modal mt1rem mb6rem">
            <button name="<?= $_SESSION['user_type'] === 'student' ? 'toMain' : 'toBack' ?>"
                    class="register__modal-link">Вернуться
            </button>
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
<script>window.addEventListener('DOMContentLoaded', () => {
        const button = document.querySelector('button.register__modal-link');
        button.addEventListener('click', redirect);

        function redirect() {
            if (button.name === 'toBack') {
                window.history.back();
            } else {
                window.location.href = '/student/main.php';
            }
        }

    });
</script>
</html>
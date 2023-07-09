<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Auth;
use Classes\Autoloader;
use Classes\Courses;
use Classes\Database;
use Classes\Session;
use Classes\Url;

Autoloader::register();
Session::start();

if (!(Auth::checkUserType('teacher') || Auth::checkUserType('student'))) {
    Url::redirect('basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();
    $unattachedCourses = Courses::getUnattachedCourses($connection, $_SESSION['user_id']);
    $attachedCourses = Courses::getAttachedCourses($connection, $_SESSION['user_id'],);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }
        if (isset($_POST['attachCourses'])) {
            foreach ($_POST['attachCourses'] as $course) {
                Courses::attachCourseToUser($connection, $_SESSION['user_id'], $course);
            }
        }
        if (isset($_POST['detachCourses'])) {
            foreach ($_POST['detachCourses'] as $course) {
                Courses::detachCourseFromUser($connection, $_SESSION['user_id'], $course);
            }
        }
        Url::redirect(substr($_SERVER['PHP_SELF'], 1), queryString: $_SERVER['QUERY_STRING']);
    }
} catch (PDOException $e) {
    $error = "Произошла ошибка базы данных: " . $e->getMessage();
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
            <?php
            if ($_SESSION['user_type'] === 'teacher') : ?>
                <a href="/teacher/main.php" class="tabs-tab">Курсы</a>
            <?php
            elseif ($_SESSION['user_type'] === 'student'): ?>
                <a href="/student/main.php" class="tabs-tab">Домашние задания</a>
            <?php
            endif ?>
            <li>
                <a class="tabs-tab tabs-tab_active">Запросить доступ</a>
            </li>
        </ul>
        <form method="post">
            <button type="submit" name="logout" class="header__button-login">Выйти</button>
        </form>
    </div>
</header>
<main class="dark-grey-background">
    <div class="main__content">
        <div class="login__modal mt6rem mb6rem">
            <div class="login__header fs18">Запрос</div>
            <form class="login__form accessRequestForm" method="post">
                <div class="dropdown-check-list" tabindex="100">
                    <span class="anchor">Прикрепить к курсу</span>
                    <ul class="items">
                        <?php
                        foreach ($unattachedCourses as $course): ?>
                            <li><input type="checkbox" name="attachCourses[]"
                                       value="<?= $course['id'] ?>"/><?= $course['title'] ?></li>
                        <?php
                        endforeach; ?>
                    </ul>
                </div>
                <div class="dropdown-check-list mt1rem mb1rem" tabindex="100">
                    <span class="anchor">Открепить от курса</span>
                    <ul class="items">
                        <?php
                        foreach ($attachedCourses as $course): ?>
                            <li><input type="checkbox" name="detachCourses[]"
                                       value="<?= $course['id'] ?>"/><?= $course['title'] ?></li>
                        <?php
                        endforeach; ?>
                    </ul>
                </div>
                <button type="submit" class="register__modal-link">Отправить</button>
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
<script src="/js/script.js"></script>
</body>
</html>
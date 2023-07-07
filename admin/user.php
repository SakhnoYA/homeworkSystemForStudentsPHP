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
    Url::redirect('basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }
        if (isset($_POST['toDelete'])) {
            Users::delete($connection, ['id' => $_GET['id']]);
            Url::redirect('admin/main.php');
        }
        if (isset($_POST['last_name'])) {
            Users::update(
                $connection,
                array_intersect_key($_POST, array_flip(['first_name', 'middle_name', 'last_name'])),
                ['id' => $_GET['id']]
            );
        }
        if (isset($_POST['attachCourses'])) {
            foreach ($_POST['attachCourses'] as $course) {
                Courses::attachCourseToUser($connection, $_GET['id'], $course,true);
            }
        }
        if (isset($_POST['detachCourses'])) {
            foreach ($_POST['detachCourses'] as $course) {
                Courses::detachCourseFromUser($connection, $_GET['id'], $course);
            }
        }
        Url::redirect(substr($_SERVER['PHP_SELF'], 1), queryString: $_SERVER['QUERY_STRING']);
    }
    if (isset($_GET['id'])) {
        $user = Users::getWithJoinUserType(
            $connection,
            ['first_name', 'middle_name', 'last_name'],
            ['id' => $_GET['id']]
        );
        $unattachedCourses = Courses::getUnattachedCourses($connection, $_GET['id']);
        $attachedCourses = Courses::getAttachedCourses($connection, $_GET['id']);
    } else {
        die("No Id in query parameter");
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
            <li>
                <a href="main.php" class="tabs-tab">Пользователи</a>
            </li>
            <li>
                <a href="registrations.php" class="tabs-tab">Регистрации</a>
            </li>
            <li>
                <a  class="tabs-tab">Создание курса</a>
            </li>
            <li>
                <a href="accessRequests.php" class="tabs-tab">Запросы доступа</a>
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
            <div class="login__header">Профиль</div>
            <form method="post">
                <label>
                    Фамилия
                    <input type="text" name="last_name" class="login__form-input"
                           value="<?= $user[0]['last_name'] ?>">
                </label>
                <label>
                    Имя
                    <input type="text" name="first_name" class="login__form-input"
                           value="<?= $user[0]['first_name'] ?>">
                </label>
                <label>
                    Отчество
                    <input type="text" name="middle_name" class="login__form-input"
                           value="<?= $user[0]['middle_name'] ?>">
                </label>
                <div class="role">Тип пользователя: <?= $user[0]['readable_name'] ?></div>
                <div class="dropdown-check-list mt1rem" tabindex="100">
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
                <div class="dropdown-check-list mt1rem" tabindex="100">
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
                <button class="enter__link bg-red mt1rem" name="toDelete">Удалить
                </button>
                <button type="submit" class="enter__link mt1rem">Сохранить</button>
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
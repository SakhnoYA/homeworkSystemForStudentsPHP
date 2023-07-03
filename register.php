<?php

require_once 'Classes/Autoloader.php';

use Classes\Auth;
use Classes\Autoloader;
use Classes\Database;
use Classes\Session;
use Classes\Url;
use Classes\Users;

Autoloader::register();
Session::start();

//if (Auth::isAuthenticated()){
//    Url::redirect($_SESSION['user_type'] . '/main.php');
//}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $connection = (new Database())->getDbConnection();
        Users::create($connection, $_POST);
        Url::redirect('index.php');
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
    <link rel="stylesheet" href="css/style.css">
    <link rel="apple-touch-icon" sizes="57x57" href="icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="icons/favicon-16x16.png">
    <link rel="manifest" href="icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="icons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <a href="index.php" class="header__content">
        <img src="images/logo.png" class="header-logo" alt="Homework System logo">
    </a>
</header>
<main class="dark-grey-background">
    <div class="main__content">
        <div class="login__modal mt6rem">
            <div class="login__header fs18">Регистрация Homework System</div>
            <form class="login__form" method="post">
                <input type="number" name="id" class="login__form-input" placeholder="ID" required>
                <input type="text" name="first_name" class="login__form-input" placeholder="Имя" required>
                <input type="text" name="last_name" class="login__form-input" placeholder="Фамилия" required>
                <input type="text" name="middle_name" class="login__form-input" placeholder="Отчество">
                <input type="password" name="password" id="password" class="login__form-input" placeholder="Пароль"
                       required>

                <div class="radio">
                    <label class="radio-label">
                        <input type="radio" name="type" required checked value="2">
                        Ученик
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="type" required value="3">
                        Преподаватель
                    </label>
                </div>
                <button type="submit" class="register__modal-link">Зарегистрироваттся</button>
            </form>
            <?php
            if (!empty($error)) : ?>
                <p class="errorMessage"><?= $error ?></p>
            <?php
            endif; ?>
        </div>
        <div class="register__modal mt1rem mb6rem">
            <div class="register__header">Или</div>
            <a href="index.php" class="enter__link">Войти</a>
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
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Autoloader;
use Classes\Database;
use Classes\Session;
use Classes\Url;
use Classes\Users;

Autoloader::register();
Session::start();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $connection = (new Database())->getDbConnection();
        Users::create($connection, $_POST);
        Url::redirect('index.php');
    }
} catch (PDOException $e) {
    die("Произошла ошибка базы данных: " . $e->getMessage());
}
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/head.html' ?>
    <header>
        <a href="../index.php" class="header__content">
            <img src="../images/logo.png" class="header-logo" alt="Homework System logo">
        </a>
    </header>
    <main class="dark-grey-background">
        <div class="main__content">
            <div class="login__modal mt4rem">
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
                    <button type="submit" class="register__modal-link">Зарегистрироваться</button>
                </form>
            </div>
            <div class="register__modal mt1rem mb6rem">
                <div class="register__header">Или</div>
                <a href="../index.php" class="enter__link">Войти</a>
            </div>
        </div>
    </main>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
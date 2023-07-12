<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/Classes/Autoloader.php';

use php\Classes\Auth;
use php\Classes\Autoloader;
use php\Classes\Database;
use php\Classes\Session;
use php\Classes\Url;

Autoloader::register();
Session::start();


if (Auth::isAuthenticated()) {
    Url::redirect('php/' . $_SESSION['user_type'] . '/main.php');
}

try {
    if (isset($_POST['toEnter'])) {
        $connection = (new Database())->getDbConnection();
        if (
            Auth::authenticate(
                $connection,
                $_POST['login'],
                $_POST['password']
            )
        ) {
            if (Auth::isConfirmed($connection, $_POST['login'])) {
                Auth::login($connection, $_POST['login']);
                Url::redirect('php/' . $_SESSION['user_type'] . '/main.php');
            } else {
                $error = "Ваш профиль не подтвержден администратором";
            }
        } else {
            $error = "Неверные входные данные";
        }
    }
} catch (PDOException $e) {
    $error = "Произошла ошибка базы данных: " . $e->getMessage();
} catch (TypeError $typeError) {
    $error = "Произошла ошибка формата данных: " . $typeError->getMessage();
}
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/head.html' ?>
    <header>
        <div class="header__content">
            <img src="images/logo.png" class="header-logo" alt="Homework System logo">
        </div>
    </header>
    <main class="dark-grey-background">
        <div class="main__content">
            <div class="login__modal mt4rem">
                <div class="login__header">Вход Homework System</div>
                <form action="" class="login__form" method="post">
                    <input type="number" name="login" class="login__form-input" placeholder="ID">
                    <input type="password" name="password" class="login__form-input" placeholder="Пароль">
                    <button type="submit" name="toEnter" class="enter__link">Войти</button>
                </form>
                <?php
                if (!empty($error)) : ?>
                    <p class="errorMessage"><?= $error ?></p>
                    <?php
                endif; ?>
            </div>
            <div class="register__modal mt1rem">
                <div class="register__header">Или</div>
                <a href="/php/basic/register.php" class="register__modal-link">Зарегистрироваться</a>
            </div>
        </div>
    </main>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
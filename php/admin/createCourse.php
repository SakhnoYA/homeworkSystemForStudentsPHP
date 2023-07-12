<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/Classes/Autoloader.php';

use php\Classes\Auth;
use php\Classes\Autoloader;
use php\Classes\Courses;
use php\Classes\Database;
use php\Classes\Session;
use php\Classes\Url;

Autoloader::register();
Session::start();

if (!Auth::checkUserType('admin')) {
    Url::redirect('php/basic/forbidden.php');
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }

        if (isset($_POST['toSave'])) {
            $connection = (new Database())->getDbConnection();
            Courses::create($connection, array_filter($_POST, static fn($value) => $value !== ''));

            Url::redirect(substr($_SERVER['PHP_SELF'], 1));
        }
    }
} catch (PDOException $e) {
    die("Произошла ошибка базы данных: " . $e->getMessage());
}
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/head.html' ?>
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
                    <a class="tabs-tab tabs-tab_active">Создание курса</a>
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
                <div class="login__header">Создать курс</div>
                <form action="" class="login__form" method="post">
                    <input type="text" name="title" class="login__form-input" required placeholder="Название">
                    <label class="label-input">
                        Дата начала
                        <input type="date" name="start_date" class="login__form-input mt7px" value="<?php
                        echo date('Y-m-d'); ?>">
                    </label>
                    <label class="label-input">
                        Дата конца
                        <input type="date" name="end_date" class="login__form-input mt7px">
                    </label>
                    <label class="label-input mb16px">
                        <input type="checkbox" name="availability" checked="checked">
                        Доступен
                    </label>

                    <label class="label-input "> Категория <select name="category" class="login__form-input mt7px">
                            <option></option>
                            <option
                                    value="Естественные науки">Естественные науки
                            </option>
                            <option
                                    value="Точные науки">Точные науки
                            </option>
                            <option
                                    value="Технические науки">Технические науки
                            </option>
                            <option
                                    value="Социально-гуманитарные науки">Социально-гуманитарные науки
                            </option>
                        </select></label>
                    <label class="label-input "> Сложность <select name="difficulty_level"
                                                                   class="login__form-input mt7px">
                            <option></option>
                            <option
                                    value="Легкий уровень">Легкий уровень
                            </option>
                            <option
                                    value="Средний уровень">Средний уровень
                            </option>
                            <option
                                    value="Сложный уровень">Сложный уровень
                            </option>
                        </select></label>
                    <textarea name="description" class="login__form-input h200" maxlength="50"
                              placeholder="Описание"></textarea>
                    <input type="hidden" name="updated_by" value="<?= $_SESSION['user_id'] ?>">
                    <button type="submit" name="toSave" class="enter__link">Создать</button>
                </form>
            </div>
        </div>
    </main>
    <script src="/js/checklist.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
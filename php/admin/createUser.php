<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/Classes/Autoloader.php';

use php\Classes\Auth;
use php\Classes\Autoloader;
use php\Classes\Courses;
use php\Classes\Database;
use php\Classes\Session;
use php\Classes\Url;
use php\Classes\Users;

Autoloader::register();
Session::start();

if (!Auth::checkUserType('admin')) {
    Url::redirect('php/basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();
    $courses = Courses::get($connection, ['id', 'title']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }

        if (isset($_POST['toSave'])) {
            Users::create(
                $connection,
                array_intersect_key(
                    $_POST,
                    array_flip(['id', 'first_name', 'middle_name', 'last_name', 'password', 'type', 'is_confirmed'])
                ),
            );
            if (isset($_POST['attachCourses'])) {
                foreach ($_POST['attachCourses'] as $course) {
                    Courses::attachCourseToUser($connection, $_POST['id'], $course, true);
                }
            }
            if (isset($_POST['detachCourses'])) {
                foreach ($_POST['detachCourses'] as $course) {
                    Courses::detachCourseFromUser($connection, $_POST['id'], $course);
                }
            }
        }
        Url::redirect(substr($_SERVER['PHP_SELF'], 1), queryString: $_SERVER['QUERY_STRING']);
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
                    <a href="createCourse.php" class="tabs-tab">Создание курса</a>
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
                <div class="login__header fs18">Профиль</div>
                <form class="login__form" method="post">
                    <input type="number" name="id" class="login__form-input" placeholder="ID" required>
                    <input type="text" name="first_name" class="login__form-input" placeholder="Имя" required>
                    <input type="text" name="last_name" class="login__form-input" placeholder="Фамилия" required>
                    <input type="text" name="middle_name" class="login__form-input" placeholder="Отчество">
                    <input type="password" name="password" id="password" class="login__form-input" placeholder="Пароль"
                           required>
                    <div class="dropdown-check-list mb1rem" tabindex="100">
                        <span class="anchor">Прикрепить к курсу</span>
                        <ul class="items">
                            <?php
                            foreach ($courses as $course) : ?>
                                <li><input type="checkbox" name="attachCourses[]"
                                           value="<?= $course['id'] ?>"/><?= $course['title'] ?></li>
                            <?php
                            endforeach; ?>
                        </ul>
                    </div>
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
                    <label class="label-input fs15 mb16px">
                        <input type="checkbox" required checked="checked">
                        Я согласен(а) с <a class="policy" href="../basic/policy.html">политикой конфиденциальности</a>
                    </label>
                    <input type="hidden" name="is_confirmed" value="1">
                    <button type="submit" name="toSave" class="register__modal-link">Сохранить</button>
                </form>
            </div>
        </div>
    </main>
    <script src="/js/checklist.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
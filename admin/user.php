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

    if (isset($_GET['id'])) {
        $user = Users::getWithJoinUserType(
            $connection,
            ['first_name', 'middle_name', 'last_name'],
            ['id' => $_GET['id']]
        );
        $unattachedCourses = Courses::getUnattachedCourses($connection, $_GET['id']);
        $attachedCourses = Courses::getAttachedCourses($connection, $_GET['id']);
    } else {
        die("Нет Id в query параметре");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }

        if (isset($_POST['toDelete'])) {
            Users::delete($connection, ['id' => $_GET['id']]);
            Url::redirect('admin/main.php');
        }

        if (isset($_POST['toSave'])) {
            Users::update(
                $connection,
                array_intersect_key($_POST, array_flip(['first_name', 'middle_name', 'last_name'])),
                ['id' => $_GET['id']]
            );
        }

        if (isset($_POST['attachCourses'])) {
            foreach ($_POST['attachCourses'] as $course) {
                Courses::attachCourseToUser($connection, $_GET['id'], $course, true);
            }
        }

        if (isset($_POST['detachCourses'])) {
            foreach ($_POST['detachCourses'] as $course) {
                Courses::detachCourseFromUser($connection, $_GET['id'], $course);
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
                    <a class="tabs-tab">Создание курса</a>
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
                            foreach ($unattachedCourses as $course) : ?>
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
                            foreach ($attachedCourses as $course) : ?>
                                <li><input type="checkbox" name="detachCourses[]"
                                           value="<?= $course['id'] ?>"/><?= $course['title'] ?></li>
                                <?php
                            endforeach; ?>
                        </ul>
                    </div>
                    <button class="enter__link bg-red mt1rem" name="toDelete">Удалить
                    </button>
                    <button type="submit" name="toSave" class="enter__link mt1rem">Сохранить</button>
                </form>
            </div>
        </div>
    </main>
    <script src="/js/script.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
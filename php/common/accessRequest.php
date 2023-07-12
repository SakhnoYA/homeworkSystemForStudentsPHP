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

if (!(Auth::checkUserType('teacher') || Auth::checkUserType('student'))) {
    Url::redirect('php/basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();
    $unattachedCourses = Courses::getUnattachedCourses($connection, $_SESSION['user_id']);
    $attachedCourses = Courses::getAttachedCourses($connection, $_SESSION['user_id']);

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
                    <a href="<?= $_SESSION['user_type'] === 'teacher' ? '/php/teacher/main.php' : '/php/student/main.php' ?>"
                       class="
                   tabs-tab">Курсы</a>
                </li>
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
                            foreach ($unattachedCourses as $course) : ?>
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
                            foreach ($attachedCourses as $course) : ?>
                                <li><input type="checkbox" name="detachCourses[]"
                                           value="<?= $course['id'] ?>"/><?= $course['title'] ?></li>
                                <?php
                            endforeach; ?>
                        </ul>
                    </div>
                    <button type="submit" class="register__modal-link">Отправить</button>
                </form>
            </div>
        </div>
    </main>
    <script src="/js/checklist.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
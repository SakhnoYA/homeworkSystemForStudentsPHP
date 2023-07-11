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

if (!Auth::checkUserType('admin')) {
    Url::redirect('basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();
    $courseRelationships = Courses::getUnconfirmedUserCourseRelationships($connection);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }

        if (isset($_POST['confirmation'])) {
            if ($_POST['confirmation'] === "confirmed") {
                Courses::confirmUserCourseRelationship($connection, $_POST['user_id'], $_POST['course_id']);
            } elseif ($_POST['confirmation'] === "declined") {
                Courses::deleteUserCourseRelationship($connection, $_POST['user_id'], $_POST['course_id']);
            }
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
                    <a href="registrations.php" class="tabs-tab ">Регистрации</a>
                </li>
                <li>
                    <a href="createCourse.php" class="tabs-tab">Создание курса</a>
                </li>
                <li>
                    <a class="tabs-tab tabs-tab_active">Запросы доступа</a>
                </li>
            </ul>
            <form method="post">
                <button type="submit" name="logout" class="header__button-login">Выйти</button>
            </form>
        </div>
    </header>
    <main class="dark-grey-background">
        <div class="main__content">
            <div class="login__modal mt6rem mb6rem width-auto dark-slay-gray padding-20-20 ">
                <?php
                if (empty($courseRelationships)) : ?>
                    Запросы доступа к курсам отсутствуют
                    <?php
                else : ?>
                    <table class="tg">
                        <thead>
                        <tr>
                            <th class="tg-amwm">ID</th>
                            <th class="tg-amwm">Имя</th>
                            <th class="tg-amwm">Фамилия</th>
                            <th class="tg-amwm">Отчество</th>
                            <th class="tg-amwm">Тип пользователя</th>
                            <th class="tg-amwm">Курс</th>
                            <th class="tg-amwm"></th>
                            <th class="tg-amwm"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $isOdd = true;
                        foreach ($courseRelationships as $courseRelationship) :
                            $rowClass = $isOdd ? 'tg-0lax' : 'tg-hmp3';
                            $isOdd = !$isOdd;
                            ?>
                            <tr>
                                <td class="<?= $rowClass ?>"><?= $courseRelationship['user_id'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $courseRelationship['first_name'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $courseRelationship['last_name'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $courseRelationship['middle_name'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $courseRelationship['readable_name'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $courseRelationship['title'] ?></td>
                                <form method="post">
                                    <input type="hidden" name="user_id" value="<?= $courseRelationship['user_id'] ?>">
                                    <input type="hidden" name="course_id"
                                           value="<?= $courseRelationship['course_id'] ?>">
                                    <td class="<?= $rowClass ?>">
                                        <button class="table-button" name="confirmation" value="confirmed">Подтвердить
                                        </button>
                                    </td>
                                    <td class="<?= $rowClass ?>">
                                        <button class="table-button" name="confirmation" value="declined">Удалить
                                        </button>
                                    </td>
                                </form>
                            </tr>
                            <?php
                        endforeach; ?>
                        </tbody>
                    </table>
                    <?php
                endif; ?>
            </div>
        </div>
    </main>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
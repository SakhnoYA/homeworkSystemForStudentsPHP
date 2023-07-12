<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/Classes/Autoloader.php';

use php\Classes\Attempts;
use php\Classes\Auth;
use php\Classes\Autoloader;
use php\Classes\Database;
use php\Classes\Session;
use php\Classes\Url;

Autoloader::register();
Session::start();

if (!(Auth::checkUserType('student') || Auth::checkUserType('teacher'))) {
    Url::redirect('php/basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();
    if (isset($_GET['attempt_id'])) {
        $attemptContent = Attempts::getContent($connection, base64_decode(urldecode($_GET['attempt_id'])));
    } else {
        die("Нет Id в query параметре");
    }
    if (isset($_POST['logout'])) {
        Session::destroySession();
        Url::redirect('index.php');
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
                    <a class="tabs-tab ">Запросить доступ</a>
                </li>
            </ul>
            <form method="post">
                <button type="submit" name="logout" class="header__button-login">Выйти</button>
            </form>
        </div>
    </header>
    <main class="dark-grey-background">
        <div class="main__content mt4rem">
            <?php
            foreach ($attemptContent as $task) : ?>
                <div class="login__modal w40p  mb1rem  <?= $task['is_correct'] ? 'correct' : 'wrong' ?> ">
                    <div class="login__header"><?= $task['title'] ?></div>
                    <p><?= $task['description'] ?></p>
                    <div class="role">Ответ студента: <?= $task['user_input'] ?></div>
                    <div class="role">Правильный ответ: <?= $task['answer'] ?></div>
                    <div class="role">Количество баллов: <?= $task['max_score'] ?></div>
                </div>
                <?php
            endforeach; ?>
            <div class="register__modal mt1rem mb6rem">
                <button name="<?= $_SESSION['user_type'] === 'student' ? 'toMain' : 'toBack' ?>"
                        class="register__modal-link">Вернуться
                </button>
            </div>
        </div>
    </main>
    <script>window.addEventListener('DOMContentLoaded', () => {
            const button = document.querySelector('button.register__modal-link');
            button.addEventListener('click', redirect);

            function redirect() {
                if (button.name === 'toBack') {
                    window.history.back();
                } else {
                    window.location.href = '../student/main.php';
                }
            }
        });
    </script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
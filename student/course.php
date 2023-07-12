<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Auth;
use Classes\Autoloader;
use Classes\Database;
use Classes\Homeworks;
use Classes\Attempts;
use Classes\Session;
use Classes\Url;

Autoloader::register();
Session::start();

if (!Auth::checkUserType('student')) {
    Url::redirect('basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();

    if (isset($_GET['id'])) {
        $attachedHomeworks = Homeworks::getAttachedHomeworks($connection, $_GET['id']);
    } else {
        die("Нет Id в query параметре");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }

        if (isset($_POST['toSolve'])) {
            Url::redirect(
                'student/homework.php',
                queryString: "homework_id=" . $_POST['homework_id'] . "&course_id=" . $_GET['id']
            );
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
                    <a href="main.php" class="tabs-tab ">Курсы</a>
                </li>
                <li>
                    <a href="/common/accessRequest.php  " class="tabs-tab ">Запросить доступ</a>
                </li>
            </ul>
            <form method="post">
                <button type="submit" name="logout" class="header__button-login">Выйти</button>
            </form>
        </div>
    </header>
    <main class="dark-grey-background">
        <div class="main__content">
            <div class="login__modal  mt6rem mb6rem width-auto dark-slay-gray padding-20-20 ">
                <?php
                if (empty($attachedHomeworks)) : ?>
                    Домашние задания отсутствуют
                <?php
                else : ?>
                    <table class="tg">
                        <thead>
                        <tr>
                            <th class="tg-amwm">Название</th>
                            <th class="tg-amwm">Описание</th>
                            <th class="tg-amwm">Всего попыток</th>
                            <th class="tg-amwm">Всего баллов</th>
                            <th class="tg-amwm">Проходные баллы</th>
                            <th class="tg-amwm">Дата начала</th>
                            <th class="tg-amwm">Дата конца</th>
                            <th class="tg-amwm"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $isOdd = true;
                        foreach ($attachedHomeworks as $homework) :
                            $rowClass = $isOdd ? 'tg-0lax' : 'tg-hmp3';
                            $isOdd = !$isOdd;
                            ?>
                            <tr>
                                <td class="<?= $rowClass ?>"><?= $homework['title'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $homework['description'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $homework['max_attempts'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $homework['total_marks'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $homework['passing_marks'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $homework['start_date'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $homework['end_date'] ?></td>
                                <td class="<?= $rowClass ?>">
                                    <?php
                                    if (Attempts::getCount(
                                            $connection,
                                            $_SESSION['user_id'],
                                            $homework['id']
                                        ) < $homework['max_attempts']): ?>
                                        <form method="post">
                                            <input type="hidden" name="homework_id" value="<?= $homework['id'] ?>">
                                            <button class="table-button" name="toSolve">Решать
                                            </button>
                                        </form>
                                    <?php
                                    else: ?>
                                        <div class="table-button">Попытки закончились</div>
                                    <?php
                                    endif; ?>
                                </td>
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
    <script src="/js/script.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
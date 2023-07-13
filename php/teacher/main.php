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

if (!Auth::checkUserType('teacher')) {
    Url::redirect('php/basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();
    $confirmedAttachedCourses = Courses::getAttachedCourses($connection, $_SESSION['user_id'], true);

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
                    <a href="main.php" class="tabs-tab tabs-tab_active">Курсы</a>
                </li>
                <li>
                    <a href="/php/common/accessRequest.php  " class="tabs-tab ">Запросить доступ</a>
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
                if (empty($confirmedAttachedCourses)) : ?>
                    Прикрепления к курсам отсутствуют. Пожалуйста, запросите доступ.
                    <?php
                else : ?>
                    <table class="tg">
                        <thead>
                        <tr>
                            <th class="tg-amwm">ID</th>
                            <th class="tg-amwm">Название</th>
                            <th class="tg-amwm">Описание</th>
                            <th class="tg-amwm">Дата начала</th>
                            <th class="tg-amwm">Дата конца</th>
                            <th class="tg-amwm">Уровень сложности</th>
                            <th class="tg-amwm">Категория</th>
                            <th class="tg-amwm">Доступность</th>
                            <th class="tg-amwm">Время создания</th>
                            <th class="tg-amwm">Время обновления</th>
                            <th class="tg-amwm">Обновил ID</th>
                            <th class="tg-amwm"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $isOdd = true;
                        foreach ($confirmedAttachedCourses as $course) :
                            $rowClass = $isOdd ? 'tg-0lax' : 'tg-hmp3';
                            $isOdd = !$isOdd;
                            ?>
                            <tr>
                                <td class="<?= $rowClass ?>"><?= $course['id'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $course['title'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $course['description'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $course['start_date'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $course['end_date'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $course['difficulty_level'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $course['category'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $course['availability'] === true ? 'да' : 'нет' ?></td>
                                <td class="<?= $rowClass ?>"><?= date('Y-m-d H:i', strtotime($course['created_at'])) ?></td>
                                <td class="<?= $rowClass ?>"><?= date('Y-m-d H:i', strtotime($course['updated_at'])) ?></td>
                                <td class="<?= $rowClass ?>"><?= $course['updated_by'] ?></td>
                                <td class="<?= $rowClass ?>">
                                    <a href="course.php?id=<?= $course['id'] ?>">
                                        <button class="table-button">Управление
                                        </button>
                                    </a>
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
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
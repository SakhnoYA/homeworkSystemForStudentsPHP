<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Auth;
use Classes\Autoloader;
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
    $users = Users::getWithJoinUserType($connection, optionsWHERE: ['is_confirmed' => 0]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }

        if (isset($_POST['confirmation'])) {
            if ($_POST['confirmation'] === "confirmed") {
                Users::update($connection, ['is_confirmed' => 1], ['id' => $_POST['id']]);
            } elseif ($_POST['confirmation'] === "declined") {
                Users::delete($connection, ['id' => $_POST['id']]);
            }
        }

        if (isset($_POST['deleteUnconfirmedUsers'])) {
            Users::deleteUnconfirmedUsers($connection);
        }

        Url::redirect(substr($_SERVER['PHP_SELF'], 1));
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
                    <a class="tabs-tab tabs-tab_active">Регистрации</a>
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
        <div class="header__subcontent">
            <form method="post">
                <button type="submit" name="deleteUnconfirmedUsers" class=" header__button-login  fs17">Удалить всех
                    неподтвержденных пользователей
                </button>
            </form>
        </div>
    </header>
    <main class="dark-grey-background">
        <div class="main__content">
            <div class="login__modal mt6rem mb6rem width-auto dark-slay-gray padding-20-20 ">
                <?php
                if (empty($users)) : ?>
                    Неподтвержденные регистрации отсутствуют
                    <?php
                else : ?>
                    <table class="tg">
                        <thead>
                        <tr>
                            <th class="tg-amwm">ID</th>
                            <th class="tg-amwm">Дата регистрации</th>
                            <th class="tg-amwm">Имя</th>
                            <th class="tg-amwm">Фамилия</th>
                            <th class="tg-amwm">Отчество</th>
                            <th class="tg-amwm">Тип пользователя</th>
                            <th class="tg-amwm"></th>
                            <th class="tg-amwm"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $isOdd = true;
                        foreach ($users as $user) :
                            $rowClass = $isOdd ? 'tg-0lax' : 'tg-hmp3';
                            $isOdd = !$isOdd;
                            ?>
                            <tr>
                                <td class="<?= $rowClass ?>"><?= $user['id'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $user['registration_date'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $user['first_name'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $user['last_name'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $user['middle_name'] ?></td>
                                <td class="<?= $rowClass ?>"><?= $user['readable_name'] ?></td>
                                <form method="post">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
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
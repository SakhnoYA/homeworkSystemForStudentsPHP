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
    Url::redirect('forbidden.php');
}

//try {
//} catch (PDOException $e) {
//    $error = "Произошла ошибка базы данных: " . $e->getCode();
//}

$connection = (new Database())->getDbConnection();

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
        Url::redirect(substr($_SERVER['PHP_SELF'], 1));
    }
    if (isset($_POST['deleteUnconfirmedUsers'])) {
        Users::deleteUnconfirmedUsers($connection);
    }
}


$users = Users::getWithJoinUserType($connection, optionsWHERE: ['is_confirmed' => 0]);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Домашние задания - Homework System</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
    <link rel="manifest" href="/icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="icons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
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
                неподтвежденных пользователей
            </button>
        </form>
    </div>
</header>
<main class="dark-grey-background">
    <div class="main__content">
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
            foreach ($users as $user):
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
    </div>
</main>
<footer>
    <div class="footer">
        <div class="footer-copyright">© 2023 Ярослав Сахно</div>
        <a class="github_link" href="https://github.com/SakhnoYA">Мой гитхаб</a>
    </div>
</footer>

</body>
</html>
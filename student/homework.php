<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Auth;
use Classes\Autoloader;
use Classes\Database;
use Classes\Homeworks;
use Classes\Session;
use Classes\Tasks;
use Classes\Url;

Autoloader::register();
Session::start();

if (!Auth::checkUserType('student')) {
    Url::redirect('basic/forbidden.php');
}

$connection = (new Database())->getDbConnection();
//try {
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        Session::destroySession();
        Url::redirect('index.php');
    }
}

if (isset($_GET['homework_id'])) {
    $attachedTasks = Tasks::getAttachedTasks(
        $connection,
        $_GET['homework_id']
    );
} else {
    die("No Id in query parameter");
}

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

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
                <a href="../common/main.php" class="tabs-tab">Пользователи</a>
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
    <div class="main__content mt4rem">
        <?php
        foreach ($attachedTasks as $task): ?>
            <div class="login__modal w40p  mb1rem ">
                <div class="login__header"><?= $task['title'] ?></div>
                <form method="post" class="login__form">
                    <p><?= $task['description'] ?></p>
                    <?php
                    if ($task['type'] === 'single_choice'): ?>
                        <div class="radio flex-radio">
                            <?php
                            foreach (explode(',', substr($task['options'], 1, -1)) as $option): ?>
                                <label class="radio-label mb5px">
                                    <input type="radio" name="user_input[]" value="<?= $option ?>">
                                    <?= $option ?>
                                </label>
                            <?php
                            endforeach; ?>
                        </div>
                    <?php
                    elseif ($task['type'] === 'multiple_choice'): ?>
                        <div class="radio flex-radio">
                            <?php
                            foreach (explode(',', substr($task['options'], 1, -1)) as $option): ?>
                                <label class="label-input mb5px">
                                    <input type="checkbox" name="user_input[]" value="<?= $option ?>"/>
                                    <?= $option ?>
                                </label>
                            <?php
                            endforeach; ?>
                        </div>
                    <?php
                    else: ?>
                        <input type="text" class="login__form-input" name="user_input[]" placeholder="Ответ"/>
                    <?php
                    endif ?>
                    <?php
                    if (isset($task['max_score'])):
                        ?>
                        <div class="role">Количество баллов: <?= $task['max_score'] ?></div>
                    <?php
                    endif ?>
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                </form>
                <?php
                if (!empty($error)) : ?>
                    <p class="errorMessage"><?= $error ?></p>
                <?php
                endif; ?>
            </div>
        <?php
        endforeach; ?>
        <div class="register__modal mt1rem mb6rem">
            <button name="toSendHomework" class="register__modal-link">Отправить
            </button>
        </div>
    </div>
</main>
<footer>
    <div class="footer">
        <div class="footer-copyright">© 2023 Ярослав Сахно</div>
        <a class="github_link" href="https://github.com/SakhnoYA">Мой гитхаб</a>
    </div>
</footer>
<script src="/js/checkHomework.js"></script>
</body>
</html>
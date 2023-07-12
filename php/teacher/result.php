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

if (!Auth::checkUserType('teacher')) {
    Url::redirect('php/basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();
    $attempts = Attempts::getByHomework($connection, $_GET['homework_id']);

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
                <table class="tg">
                    <thead>
                    <tr>
                        <th class="tg-amwm">Студент</th>
                        <th class="tg-amwm">Попытка</th>
                        <th class="tg-amwm">Баллы</th>
                        <th class="tg-amwm">Время</th>
                        <th class="tg-amwm"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $isOdd = true;
                    foreach ($attempts as $attempt):
                        $rowClass = $isOdd ? 'tg-0lax' : 'tg-hmp3';
                        $isOdd = !$isOdd;
                        ?>
                        <tr>
                            <td class="<?= $rowClass ?>"><?= $attempt['user_id'] ?></td>
                            <td class="<?= $rowClass ?>"><?= $attempt['attempt_number'] ?></td>
                            <td class="<?= $rowClass ?>"><?= $attempt['score'] ?></td>
                            <td class="<?= $rowClass ?>"><?= date('Y-m-d H:i', strtotime($attempt['submission_time'])) ?></td>
                            <td class="<?= $rowClass ?>">
                                <a href="../common/resultHomework.php?attempt_id=<?= urlencode(
                                    base64_encode($attempt['attempt_id'])
                                ) ?>">
                                    <button class="table-button">Смотреть
                                    </button>
                                </a>
                            </td>
                        </tr>
                    <?php
                    endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
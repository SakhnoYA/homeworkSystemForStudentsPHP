<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/Classes/Autoloader.php';

use php\Classes\Auth;
use php\Classes\Autoloader;
use php\Classes\Database;
use php\Classes\Session;
use php\Classes\Tasks;
use php\Classes\Url;

Autoloader::register();
Session::start();

if (!Auth::checkUserType('student')) {
    Url::redirect('php/basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();

    if (isset($_GET['homework_id'])) {
        $attachedTasks = Tasks::getAttachedTasks(
            $connection,
            $_GET['homework_id']
        );
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
                    <a href="main.php" class="tabs-tab ">Курсы</a>
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
        <div class="main__content mt4rem">
            <?php
            foreach ($attachedTasks as $task) : ?>
                <div class="login__modal w40p  mb1rem ">
                    <div class="login__header"><?= $task['title'] ?></div>
                    <form method="post" class="login__form">
                        <p><?= $task['description'] ?></p>
                        <?php
                        if ($task['type'] === 'single_choice') : ?>
                            <div class="radio flex-radio">
                                <?php
                                foreach (explode(',', substr($task['options'], 1, -1)) as $option) : ?>
                                    <label class="radio-label mb5px">
                                        <input type="radio" name="user_input[]" value="<?= $option ?>">
                                        <?= $option ?>
                                    </label>
                                <?php
                                endforeach; ?>
                            </div>
                        <?php
                        elseif ($task['type'] === 'multiple_choice') : ?>
                            <div class="radio flex-radio">
                                <?php
                                foreach (explode(',', substr($task['options'], 1, -1)) as $option) : ?>
                                    <label class="label-input mb5px">
                                        <input type="checkbox" name="user_input[]" value="<?= $option ?>"/>
                                        <?= $option ?>
                                    </label>
                                <?php
                                endforeach; ?>
                            </div>
                        <?php
                        else : ?>
                            <input type="text" class="login__form-input" name="user_input[]" placeholder="Ответ"/>
                        <?php
                        endif ?>
                        <?php
                        if (isset($task['max_score'])) :
                            ?>
                            <div class="role">Количество баллов: <?= $task['max_score'] ?></div>
                        <?php
                        endif ?>
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                    </form>
                </div>
            <?php
            endforeach; ?>
            <form method="post" class="register__modal mt1rem mb6rem">
                <button name="toSendHomework" class="register__modal-link">Отправить
                </button>
            </form>
        </div>
    </main>
    <script src="/js/checkHomework.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
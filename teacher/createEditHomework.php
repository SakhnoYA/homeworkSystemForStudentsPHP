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

if (!Auth::checkUserType('teacher')) {
    Url::redirect('forbidden.php');
}

$connection = (new Database())->getDbConnection();
//try {
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        Session::destroySession();
        Url::redirect('index.php');
    }
    if (isset($_POST['toCreateHomework'])) {
        $_SESSION['lastHomeworkID'] = Homeworks::create(
            $connection,
            array_filter($_POST, static fn($value) => $value !== '')
        );

        Homeworks::attachHomeworkToCourse($connection, $_SESSION['lastHomeworkID'], $_GET['course_id']);

        foreach ($_SESSION['ids'] as $id) {
            Tasks::attachTaskToHomework($connection, $id, $_SESSION['lastHomeworkID']);
        }
        unset($_SESSION['ids']);
    }
    if (isset($_POST['toUpdateHomework'])) {
        Homeworks::update(
            $connection,
            array_filter($_POST, static fn($value) => $value !== ''),
            ['id' => $_GET['homework_id'] ?? $_SESSION['lastHomeworkID']]
        );
    }
    if (isset($_POST['toCreateTaskToHomework'])) {
        if (isset($_GET['homework_id'])) {
            Tasks::attachTaskToHomework(
                $connection,
                Tasks::create($connection, array_filter($_POST, static fn($value) => $value !== '')),
                $_GET['homework_id']
            );
        } else {
            $_SESSION['ids'][] = Tasks::create($connection, array_filter($_POST, static fn($value) => $value !== ''));
            $_SESSION['form1']=[];
        }
    }
    if (isset($_POST['toUpdateTask'])) {
        Tasks::update(
            $connection,
            array_filter($_POST, static fn($value, $key) => $value !== '' && $key !== 'id', ARRAY_FILTER_USE_BOTH),
            ['id' => $_POST['id']]
        );
    }
//    Url::redirect(substr($_SERVER['PHP_SELF'], 1));
}
if (!isset($_GET['course_id'])) {
    die("No Id in query parameter");
}

if (isset($_GET['homework_id'])) {
    $attachedTasks = Tasks::getAttachedTasks(
        $connection,
        $_GET['homework_id']
    );
    $homework = Homeworks::get($connection, optionsWHERE: ['id' => $_GET['homework_id']]);
} else {
    $attachedTasks = isset($_SESSION['ids']) ? Tasks::getByIds($connection, $_SESSION['ids']) : Tasks::getAttachedTasks(
        $connection,
        $_SESSION['lastHomeworkID']
    );
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
                <a href="main.php" class="tabs-tab">Пользователи</a>
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
    <div class="main__content">
        <div class="login__modal mt6rem">
            <div class="login__header"><?= isset($_SESSION['lastHomeworkID']) || isset($homework) ? "Изменить" : "Создать" ?>
                домашнее
                задание
            </div>
            <form class="login__form" method="post">
                <label class="label-input">
                    Название
                    <input type="text" name="title" class="login__form-input mt7px" required
                           value="<?= $homework[0]['title'] ?? $_SESSION['form0']['title'] ?? '' ?>">
                </label>
                <label class="label-input">
                    Максимальное число попыток
                    <input type="number" name="max_attempts" class="login__form-input mt7px"
                           value="<?= $homework[0]['max_attempts'] ?? $_SESSION['form0']['max_attempts'] ?? '' ?>">
                </label>
                <label class="label-input">
                    Баллы
                    <input type="number" name="total_marks" class="login__form-input mt7px"
                           value="<?= $homework[0]['total_marks'] ?? $_SESSION['form0']['total_marks'] ?? '' ?>">
                </label>
                <label class="label-input">
                    Проходные баллы
                    <input type="number" name="passing_marks" class="login__form-input mt7px"
                           value="<?= $homework[0]['passing_marks'] ?? $_SESSION['form0']['passing_marks'] ?? '' ?>">
                </label>
                <label class="label-input">
                    Дата начала
                    <input type="date" name="start_date" class="login__form-input mt7px"
                           value="<?= $homework[0]['start_date'] ?? $_SESSION['form0']['start_date'] ?? date(
                               'Y-m-d'
                           ) ?>">
                </label>
                <label class="label-input">
                    Дата конца
                    <input type="date" name="end_date" class="login__form-input mt7px"
                           value="<?= $homework[0]['end_date'] ?? $_SESSION['form0']['end_date'] ?? '' ?>">
                </label>

                <label class="label-input">
                    Описание
                    <textarea name="description" class="login__form-input h200 mt7px" maxlength="50"
                    ><?= $homework[0]['description'] ?? $_SESSION['form0']['description'] ?? '' ?></textarea>
                </label>
                <input type="hidden" name="updated_by" value="<?= $_SESSION['user_id'] ?>">
                <?php
                if (!(isset($_SESSION['lastHomeworkID']) || isset($homework))):?>
                    <input type="hidden" name="created_by" value=" <?= $_SESSION['user_id'] ?>">
                <?php
                endif ?>
                <button type="submit"
                        name="<?= isset($_SESSION['lastHomeworkID']) || isset($homework) ? "toUpdateHomework" : "toCreateHomework" ?>"
                        class="enter__link">
                    <?= isset($_SESSION['lastHomeworkID']) || isset($homework) ? "Сохранить" : "Создать" ?>
                </button>
            </form>
            <?php
            if (!empty($error)) : ?>
                <p class="errorMessage"><?= $error ?></p>
            <?php
            endif; ?>
        </div>
        <div class="login__modal w40p mt4rem mb4rem">
            <div class="login__header">Создать задачу</div>
            <form class="login__form" method="post">
                <input type="text" name="title" class="login__form-input" required placeholder="Название"
                       value="<?= $_SESSION['form1']['title'] ?? '' ?>">
                <div class="divwithtooltip">
                    <label class="label-input" for="type">Тип</label>
                    <div class="tooltip "> ⓘ
                        <div class="tooltip-text">
                            <ul>
                                <li>Одиночный выбор<p>Требуется выбрать один верный вариант ответа</p></li>
                                <li>Соответствие слову <p>Tребуется ввести слово и проверить его соответствие варианту
                                        ответа</p></li>
                                <li>Множественный выбор<p>Требуется выбрать верную комбинацию ответов</p></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <select id="type" name="type" class="login__form-input mt7px">
                    <option <?= isset($_SESSION['form1']['type']) && $_SESSION['form1']['type'] === 'single_choice' ? 'selected' : '' ?>
                            value="single_choice">Одиночный выбор
                    </option>
                    <option <?= isset($_SESSION['form1']['type']) && $_SESSION['form1']['type'] === 'word_match' ? 'selected' : '' ?>
                            value="word_match">
                        Соответствие слову
                    </option>
                    <option <?= isset($_SESSION['form1']['type']) && $_SESSION['form1']['type'] === 'multiple_choice' ? 'selected' : '' ?>
                            value="multiple_choice">Множественный выбор
                    </option>
                </select>
                <label class="label-input mb16px">
                    <input type="checkbox" name="is_optional"
                           checked="<?= isset($_SESSION['form1']['is_optional']) && $_SESSION['form1']['is_optional'] ? 'checked' : '' ?>"/>
                    Доступен
                </label>
                <textarea name="answer[]" class="login__form-input " required maxlength="50"
                          placeholder="Ответы через пробел"><?= $_SESSION['form1']['answers'] ?? '' ?></textarea>
                <textarea name="description" class="login__form-input h50" maxlength="50"
                          placeholder="Описание"><?= $_SESSION['form1']['description'] ?? '' ?></textarea>
                <input type="number" name="max_score" class="login__form-input"
                       placeholder="Количество баллов" value="<?= $_SESSION['form1']['max_score'] ?? '' ?>">
                <input type="hidden" name="updated_at" value="<?php
                echo date('Y-m-d'); ?>">
                <input type="hidden" name="created_by" value="<?= $_SESSION['user_id'] ?>">
                <input type="hidden" name="updated_by" value="<?= $_SESSION['user_id'] ?>">
                <button type="submit" name="toCreateTaskToHomework" class="register__modal-link">Создать</button>
            </form>
            <?php
            if (!empty($error)) : ?>
                <p class="errorMessage"><?= $error ?></p>
            <?php
            endif; ?>
        </div>
        <?php
        foreach ($attachedTasks as $task): ?>
            <div class="login__modal w40p mb4rem ">
                <div class="login__header">Задача <?= $task['id'] ?></div>
                <form method="post">
                    <label class="label-input">
                        Название
                        <input type="text" name="title" required class="login__form-input mt7px"
                               value="<?= $task['title'] ?>">
                    </label>
                    <div class="divwithtooltip">
                        <label class="label-input mt7px" for="type">Тип</label>
                        <div class="tooltip "> ⓘ
                            <div class="tooltip-text">
                                <ul>
                                    <li>Одиночный выбор<p>Требуется выбрать один верный вариант ответа</p></li>
                                    <li>Соответствие слову <p>Tребуется ввести слово и проверить его соответствие
                                            варианту
                                            ответа</p></li>
                                    <li>Множественный выбор<p>Требуется выбрать верную комбинацию ответов</p></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <select id="type" name="type" class="login__form-input mt7px">
                        <option value="single_choice" <?= $task['type'] === null ? 'selected' : '' ?> >Одиночный
                            выбор
                        </option>
                        <option value="word_match" <?= $task['type'] === null ? 'selected' : '' ?> >Соответствие слову
                        </option>
                        <option value="multiple_choice" <?= $task['type'] === null ? 'selected' : '' ?>>Множественный
                            выбор
                        </option>
                    </select>
                    <label class="label-input mb16px">
                        <input type="checkbox" name="is_optional"
                               checked="<?= $task['is_optional'] ? 'checked' : '' ?>"/>
                        Доступен
                    </label>

                    <label class="label-input">
                        Ответ
                        <textarea name="answer[]" class="login__form-input mt7px" maxlength="50"
                                  required><?= preg_replace(
                                '/,/',
                                ' ',
                                substr($task['answer'], 1, -1)
                            ) ?></textarea>
                    </label> <label class="label-input">
                        Описание
                        <textarea name="description" class="login__form-input h50 mt7px" maxlength="50"
                        ><?= $task['description'] ?></textarea>
                    </label>
                    <label>
                        Количество баллов
                        <input type="number" name="max_score" class="login__form-input mt7px"
                               value="<?= $task['max_score'] ?>">
                    </label>
                    <input type="hidden" name="updated_by" value="<?= $_SESSION['user_id'] ?>">
                    <input type="hidden" name="updated_at" value="<?php
                    echo date('Y-m-d'); ?>">
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                    <button type="submit" name="toUpdateTask" class="enter__link mt1rem">Сохранить</button>
                </form>
                <?php
                if (!empty($error)) : ?>
                    <p class="errorMessage"><?= $error ?></p>
                <?php
                endif; ?>
            </div>
        <?php
        endforeach; ?>
    </div>
</main>
<footer>
    <div class="footer">
        <div class="footer-copyright">© 2023 Ярослав Сахно</div>
        <a class="github_link" href="https://github.com/SakhnoYA">Мой гитхаб</a>
    </div>
</footer>
<script src="/js/homework.js"></script>
</body>
</html>
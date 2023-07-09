<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Auth;
use Classes\Autoloader;
use Classes\Courses;
use Classes\Database;
use Classes\Homeworks;
use Classes\Session;
use Classes\Url;

Autoloader::register();
Session::start();

if (!Auth::checkUserType('teacher')) {
    Url::redirect('basic/forbidden.php');
}

//try {
$connection = (new Database())->getDbConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        Session::destroySession();
        Url::redirect('index.php');
    }
    if (isset($_POST['toCreateHomework'])) {
        $_SESSION['ids'] = [];
        unset($_SESSION['lastHomeworkID'], $_SESSION['form0'], $_SESSION['form1']);
        Url::redirect('teacher/createEditHomework.php', queryString: "course_id=" . $_GET['id']);
    }
    if (isset($_POST['toUpdateHomework'])) {
        $_SESSION['ids'] = [];
        unset($_SESSION['lastHomeworkID']);
        Url::redirect(
            'teacher/createEditHomework.php',
            queryString: "course_id=" . $_GET['id'] . "&homework_id=" . $_POST['homework_id']
        );
    }
    if (isset($_POST['toDeleteHomework'])) {
        Homeworks::delete($connection, ['id' => $_POST['homework_id']]);
    }
    if (isset($_POST['toResultCourse'])) {
        Url::redirect(
            'teacher/result.php',
            queryString: "homework_id=" . $_POST['homework_id']
        );
    }
    if (isset($_POST['toUpdateCourse'])) {
        $connection = (new Database())->getDbConnection();
        Courses::update(
            $connection,
            array_filter($_POST, static fn($value) => $value !== ''),
//                array_intersect_key($_POST, array_flip(['first_name', 'middle_name', 'last_name'])),
            ['id' => $_GET['id']]
        );
    }
    Url::redirect(substr($_SERVER['PHP_SELF'], 1), queryString: $_SERVER['QUERY_STRING']);
}
if (isset($_GET['id'])) {
    $course = Courses::get(
        $connection,
        ['title', 'description', 'start_date', 'end_date', 'difficulty_level', 'category', 'availability'],
        ['id' => $_GET['id']]
    );
    $attachedHomeworks = Homeworks::getAttachedHomeworks($connection, $_GET['id']);
} else {
    die("No Id in query parameter");
}
//} catch (PDOException $e) {
//    $error = "Произошла ошибка базы данных: " . $e->getCode();
//}


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
                <a href="main.php" class="tabs-tab">Курсы</a>
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
        <div class="login__modal mt6rem">
            <div class="login__header">Курс</div>
            <form method="post">
                <label>
                    Название
                    <input type="text" name="title" class="login__form-input mt7px"
                           value="<?= $course[0]['title'] ?>">
                </label>
                <label>
                    Дата начала
                    <input type="date" name="start_date" class="login__form-input mt7px"
                           value="<?= $course[0]['start_date'] ?>">
                </label>
                <label>
                    Дата конца
                    <input type="date" name="end_date" class="login__form-input mt7px"
                           value="<?= $course[0]['end_date'] ?>">
                </label>
                <label class="label-input mb16px">
                    <input type="checkbox" name="availability"
                           checked="<?= $course[0]['availability'] ? 'checked' : '' ?>"/>
                    Доступен
                </label>
                <label class="label-input "> Категория <select name="category" class="login__form-input mt7px">
                        <option <?= $course[0]['category'] === null ? 'selected' : '' ?> ></option>
                        <option <?= $course[0]['category'] === 'natural' ? 'selected' : '' ?>
                                value="natural">Естественные науки
                        </option>
                        <option <?= $course[0]['category'] === 'exact' ? 'selected' : '' ?>
                                value="exact">Точные науки
                        </option>
                        <option <?= $course[0]['category'] === 'technical' ? 'selected' : '' ?>
                                value="technical">Технические науки
                        </option>
                        <option <?= $course[0]['category'] === 'socialHumanities' ? 'selected' : '' ?>
                                value="socialHumanities">Социально-гуманитарные науки
                        </option>
                    </select></label>
                <label class="label-input"> Сложность <select name="difficulty_level" class="login__form-input mt7px">
                        <option <?= $course[0]['difficulty_level'] === null ? 'selected' : '' ?>></option>
                        <option <?= $course[0]['difficulty_level'] === 'easy' ? 'selected' : '' ?>
                                value="easy">Легкий уровень
                        </option>
                        <option <?= $course[0]['difficulty_level'] === 'medium' ? 'selected' : '' ?>
                                value="medium">Средний уровень
                        </option>
                        <option <?= $course[0]['difficulty_level'] === 'hard' ? 'selected' : '' ?>
                                value="hard">Сложный уровень
                        </option>
                    </select></label>
                <label class="label-input">
                    Описание
                    <textarea name="description" class="login__form-input h200 mt7px" maxlength="50"
                    ><?= $course[0]['description'] ?></textarea>
                </label>
                <input type="hidden" name="updated_by" value="<?= $_SESSION['user_id'] ?>">
                <input type="hidden" name="updated_at" value="<?php
                echo date('Y-m-d'); ?>">
                <button type="submit" class="enter__link mt1rem" name="toUpdateCourse">Сохранить</button>
            </form>
            <?php
            if (!empty($error)) : ?>
                <p class="errorMessage"><?= $error ?></p>
            <?php
            endif; ?>
        </div>
        <div class="register__modal mt1rem mb6rem">
            <form method="post" class="mb0">
                <button type="submit" name="toCreateHomework" class="register__modal-link">Создать домашнее задание
                </button>
            </form>
        </div>
        <div class="login__modal  mb6rem width-auto dark-slay-gray padding-20-20 ">
            <table class="tg">
                <thead>
                <tr>
                    <th class="tg-amwm">ID</th>
                    <th class="tg-amwm">Название</th>
                    <th class="tg-amwm">Описание</th>
                    <th class="tg-amwm">Всего попыток</th>
                    <th class="tg-amwm">Всего баллов</th>
                    <th class="tg-amwm">Проходные баллы</th>
                    <th class="tg-amwm">Дата начала</th>
                    <th class="tg-amwm">Дата конца</th>
                    <th class="tg-amwm">Дата создания</th>
                    <th class="tg-amwm">Дата обновления</th>
                    <th class="tg-amwm">Создал ID</th>
                    <th class="tg-amwm">Обновил ID</th>
                    <th class="tg-amwm"></th>
                    <th class="tg-amwm"></th>
                    <th class="tg-amwm"></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $isOdd = true;
                foreach ($attachedHomeworks as $homework):
                    $rowClass = $isOdd ? 'tg-0lax' : 'tg-hmp3';
                    $isOdd = !$isOdd;
                    ?>
                    <tr>
                        <td class="<?= $rowClass ?>"><?= $homework['id'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['title'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['description'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['max_attempts'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['total_marks'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['passing_marks'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['start_date'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['end_date'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['created_at'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['updated_at'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['created_by'] ?></td>
                        <td class="<?= $rowClass ?>"><?= $homework['updated_by'] ?></td>
                        <td class="<?= $rowClass ?>">
                            <form method="post">
                                <input type="hidden" name="homework_id" value="<?= $homework['id'] ?>">
                                <button class="table-button" name="toUpdateHomework">Редактировать
                                </button>
                            </form>
                        </td>
                        <td class="<?= $rowClass ?>">
                            <form method="post">
                                <input type="hidden" name="homework_id" value="<?= $homework['id'] ?>">
                                <button class="table-button" name="toResultCourse">Результаты
                                </button>
                            </form>
                        </td>
                        <td class="<?= $rowClass ?>">
                            <form method="post">
                                <input type="hidden" name="homework_id" value="<?= $homework['id'] ?>">
                                <button class="table-button" name="toDeleteHomework">Удалить
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php
                endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<footer>
    <div class="footer">
        <div class="footer-copyright">© 2023 Ярослав Сахно</div>
        <a class="github_link" href="https://github.com/SakhnoYA">Мой гитхаб</a>
    </div>
</footer>
<script src="/js/script.js"></script>
</body>
</html>
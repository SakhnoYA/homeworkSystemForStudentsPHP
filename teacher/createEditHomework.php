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
    Url::redirect('basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();

    if (!isset($_GET['course_id'])) {
        die("Нет Id в query параметре");
    }

    if (isset($_GET['homework_id'])) {
        $attachedTasks = Tasks::getAttachedTasks(
            $connection,
            $_GET['homework_id']
        );
        $homework = Homeworks::get($connection, optionsWHERE: ['id' => $_GET['homework_id']]);
    } else {
        $attachedTasks = Tasks::getByIds($connection, $_SESSION['ids']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }

        if (isset($_POST['toCreateHomework'])) {
            $lastHomeworkID = Homeworks::create(
                $connection,
                array_filter($_POST, static fn($value) => $value !== '')
            );

            Homeworks::attachHomeworkToCourse($connection, $lastHomeworkID, $_GET['course_id']);

            foreach ($_SESSION['ids'] as $id) {
                Tasks::attachTaskToHomework($connection, $id, $lastHomeworkID);
            }

            Url::redirect(
                substr($_SERVER['PHP_SELF'], 1),
                queryString: $_SERVER['QUERY_STRING'] . '&homework_id=' . $lastHomeworkID . '#createTaskForm'
            );
        }

        if (isset($_POST['toUpdateHomework'])) {
            Homeworks::update(
                $connection,
                array_filter($_POST, static fn($value) => $value !== ''),
                ['id' => $_GET['homework_id']]
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
                $_SESSION['ids'][] = Tasks::create(
                    $connection,
                    array_filter($_POST, static fn($value) => $value !== '')
                );
            }

            $_SESSION['form1'] = [];
        }

        if (isset($_POST['toUpdateTask'])) {
            Tasks::update(
                $connection,
                array_filter($_POST, static fn($value, $key) => $value !== '' && $key !== 'id', ARRAY_FILTER_USE_BOTH),
                ['id' => $_POST['id']]
            );
        }

        if (isset($_POST['toDeleteTask'])) {
            Tasks::delete($connection, ['id' => $_POST['id']]);
        }
        Url::redirect(substr($_SERVER['PHP_SELF'], 1), queryString: $_SERVER['QUERY_STRING'] . '#createTaskForm');
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
            <div class="login__modal mt6rem">
                <div class="login__header"><?= isset($homework) ? "Изменить" : "Создать" ?>
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
                    if (!(isset($homework))):?>
                        <input type="hidden" name="created_by" value=" <?= $_SESSION['user_id'] ?>">
                    <?php
                    endif ?>
                    <button type="submit"
                            name="<?= isset($homework) ? "toUpdateHomework" : "toCreateHomework" ?>"
                            class="enter__link">
                        <?= isset($homework) ? "Сохранить" : "Создать" ?>
                    </button>
                </form>
                <?php
                if (!empty($error)) : ?>
                    <p class="errorMessage"><?= $error ?></p>
                <?php
                endif; ?>
            </div>
            <div class="login__modal w40p mt4rem mb4rem" id="createTaskForm">
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
                                    <li>Соответствие слову <p>Tребуется ввести слово и проверить его соответствие
                                            варианту
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
                    <textarea name="description" class="login__form-input h50" maxlength="50" required
                              placeholder="Описание"><?= $_SESSION['form1']['description'] ?? '' ?></textarea>
                    <textarea name="options[]" class="login__form-input " required maxlength="50"
                              placeholder="Варианты ответа через пробел"><?= $_SESSION['form1']['options'] ?? '' ?></textarea>
                    <textarea name="answer[]" class="login__form-input " required maxlength="50"
                              placeholder="Правильные варианты ответа через пробел"><?= $_SESSION['form1']['answer'] ?? '' ?></textarea>
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
                            <option value="single_choice" <?= $task['type'] === 'single_choice' ? 'selected' : '' ?> >
                                Одиночный
                                выбор
                            </option>
                            <option value="word_match" <?= $task['type'] === 'word_match' ? 'selected' : '' ?> >
                                Соответствие
                                слову
                            </option>
                            <option value="multiple_choice" <?= $task['type'] === 'multiple_choice' ? 'selected' : '' ?>>
                                Множественный
                                выбор
                            </option>
                        </select>

                        <label class="label-input">
                            Правильный ответ
                            <textarea name="answer[]" class="login__form-input mt7px" maxlength="50"
                                      required><?= preg_replace(
                                    '/,/',
                                    ' ',
                                    substr($task['answer'], 1, -1)
                                ) ?></textarea>
                        </label>
                        <label class="label-input">
                            Варианты ответа
                            <textarea name="options[]" class="login__form-input mt7px" maxlength="50"
                                      required><?= preg_replace(
                                    '/,/',
                                    ' ',
                                    substr($task['options'], 1, -1)
                                ) ?></textarea>
                        </label>
                        <label class="label-input">
                            Описание
                            <textarea name="description" class="login__form-input h50 mt7px" maxlength="50" required
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
                        <button class="enter__link bg-red mt1rem" name="toDeleteTask">Удалить
                        </button>
                        <button type="submit" name="toUpdateTask" class="enter__link mt1rem">Сохранить</button>
                    </form>

                </div>
            <?php
            endforeach; ?>
        </div>
    </main>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const targetElement = document.getElementById(window.location.hash.slice(1))
            if (targetElement) {
                window.scrollTo(0, targetElement.offsetTop);
            }
        });
    </script>
    <script src="/js/cacheForms.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
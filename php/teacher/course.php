<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/php/Classes/Autoloader.php';

use php\Classes\Auth;
use php\Classes\Autoloader;
use php\Classes\Courses;
use php\Classes\Database;
use php\Classes\Homeworks;
use php\Classes\Session;
use php\Classes\Url;

Autoloader::register();
Session::start();

if (!Auth::checkUserType('teacher')) {
    Url::redirect('php/basic/forbidden.php');
}

try {
    $connection = (new Database())->getDbConnection();

    if (isset($_GET['id'])) {
        $course = Courses::get(
            $connection,
            ['title', 'description', 'start_date', 'end_date', 'difficulty_level', 'category', 'availability'],
            ['id' => $_GET['id']]
        );
        $attachedHomeworks = Homeworks::getAttachedHomeworks($connection, $_GET['id']);
    } else {
        die("Нет Id в query параметре");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['logout'])) {
            Session::destroySession();
            Url::redirect('index.php');
        }

        if (isset($_POST['toCreateHomework'])) {
            $_SESSION['ids'] = [];
            unset($_SESSION['lastHomeworkID'], $_SESSION['form0'], $_SESSION['form1']);
            Url::redirect('php/teacher/createEditHomework.php', queryString: "course_id=" . $_GET['id']);
        }

        if (isset($_POST['toUpdateHomework'])) {
            $_SESSION['ids'] = [];
            unset($_SESSION['lastHomeworkID']);
            Url::redirect(
                'php/teacher/createEditHomework.php',
                queryString: "course_id=" . $_GET['id'] . "&homework_id=" . $_POST['homework_id']
            );
        }

        if (isset($_POST['toDeleteHomework'])) {
            Homeworks::delete($connection, ['id' => $_POST['homework_id']]);
        }

        if (isset($_POST['toResultCourse'])) {
            Url::redirect(
                'php/teacher/result.php',
                queryString: "homework_id=" . $_POST['homework_id']
            );
        }

        if (isset($_POST['toUpdateCourse'])) {
            $connection = (new Database())->getDbConnection();
            Courses::update(
                $connection,
                array_filter($_POST, static fn($value) => $value !== ''),
                ['id' => $_GET['id']]
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
                    <a href="main.php" class="tabs-tab">Курсы</a>
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
                            <?= $course[0]['availability'] ? 'checked' : '' ?>/>
                        Доступен
                    </label>
                    <label class="label-input "> Категория <select name="category" class="login__form-input mt7px">
                            <option <?= $course[0]['category'] === null ? 'selected' : '' ?> ></option>
                            <option <?= $course[0]['category'] === 'Естественные науки' ? 'selected' : '' ?>
                                    value="Естественные науки">Естественные науки
                            </option>
                            <option <?= $course[0]['category'] === 'Точные науки' ? 'selected' : '' ?>
                                    value="Точные науки">Точные науки
                            </option>
                            <option <?= $course[0]['category'] === 'Технические науки' ? 'selected' : '' ?>
                                    value="Технические науки">Технические науки
                            </option>
                            <option <?= $course[0]['category'] === 'Социально-гуманитарные науки' ? 'selected' : '' ?>
                                    value="Социально-гуманитарные науки">Социально-гуманитарные науки
                            </option>
                        </select></label>
                    <label class="label-input"> Сложность <select name="difficulty_level"
                                                                  class="login__form-input mt7px">
                            <option <?= $course[0]['difficulty_level'] === null ? 'selected' : '' ?>></option>
                            <option <?= $course[0]['difficulty_level'] === 'Легкий уровень' ? 'selected' : '' ?>
                                    value="Легкий уровень">Легкий уровень
                            </option>
                            <option <?= $course[0]['difficulty_level'] === 'Средний уровень' ? 'selected' : '' ?>
                                    value="Средний уровень">Средний уровень
                            </option>
                            <option <?= $course[0]['difficulty_level'] === 'Сложный уровень' ? 'selected' : '' ?>
                                    value="Сложный уровень">Сложный уровень
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
            </div>
            <div class="register__modal mt1rem mb6rem">
                <form method="post" class="mb0">
                    <button type="submit" name="toCreateHomework" class="register__modal-link">Создать домашнее задание
                    </button>
                </form>
            </div>
            <div class="login__modal  mb6rem width-auto dark-slay-gray padding-20-20 ">
                <?php
                if (empty($attachedHomeworks)) : ?>
                    Домашние задания отсутствуют
                <?php
                else : ?>
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
                            <th class="tg-amwm">Время создания</th>
                            <th class="tg-amwm">Время обновления</th>
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
                        foreach ($attachedHomeworks as $homework) :
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
                                <td class="<?= $rowClass ?>"><?= date('Y-m-d H:i', strtotime($homework['created_at'])) ?></td>
                                <td class="<?= $rowClass ?>"><?= date('Y-m-d H:i', strtotime($homework['updated_at'])) ?></td>
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
                <?php
                endif; ?>
            </div>
        </div>
    </main>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
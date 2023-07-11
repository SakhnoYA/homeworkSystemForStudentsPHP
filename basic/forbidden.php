<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/Autoloader.php';

use Classes\Autoloader;
use Classes\Session;
use Classes\Url;

Autoloader::register();
Session::start();
?>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/head.html' ?>
    <header>
        <a href="../index.php" class="header__content">
            <img src="../images/logo.png" class="header-logo" alt="Homework System logo">
        </a>
    </header>
    <main class="dark-grey-background">
        <div class="main__content">
            <div class="login__modal mt6rem">
                <div class="login__header">Извините, вы не имеете доступа к этой странице.</div>
            </div>
            <div class="register__modal mt1rem ">
                <a href="/basic/register.php" class="register__modal-link">Зарегистрироваться</a>
            </div>
        </div>
    </main>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/tail.html' ?>
<?php
Url::redirect('', 4);
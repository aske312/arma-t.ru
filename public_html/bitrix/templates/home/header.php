<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

//require_once($_SERVER['DOCUMENT_ROOT'].'/catalog/update_cart_session.php');

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/resources/css/header.css");
Asset::getInstance()->addCss("/resources/css/footer.css");

// Получаем товары в корзине из сессии
session_start(); // Запуск сессии
$cartItems = isset($_SESSION['cartItems']['cartItems']) ? $_SESSION['cartItems']['cartItems'] : []; // Получаем массив товаров
$cartItemCount = array_sum(array_column($cartItems, 'quantity')); // Подсчитываем количество товаров
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Описание страницы для SEO">
    <meta name="keywords" content="Ключевые слова для SEO">
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
</head>
<body>
    <div id="panel"><?php $APPLICATION->ShowPanel(); ?></div>
    <header id="siteHeader" class="header">
        <div class="header-content">
            <div class="logo">
                <a href="/"><img src="/resources/img/logo/resource_1.png" alt="My Logo"></a>
            </div>
            <div class="nav-search">
                <button class="menu-toggle" onclick="toggleMenu()">&#9776;</button>
                <nav id="mainNav">
                    <a href="/">О компании</a>
                    <a href="/catalog/index.php?SECTION_ID=5">Каталог</a>
                    <a href="/contact/">Контакты</a>
                    <a href="/delivery/">Доставка</a>
                    <a href="/payment/">Оплата</a>
                </nav>
            </div>
            <div class="contact-info">
                <p><a href="tel:+70000000000" class="phone-link">+7 (000) 000-00-00</a></p>
                <button onclick="window.location.href='#Cash'">Оставить заявку</button>

                <div class="cart-wrapper">
                    <div class="cart-icon">
                        <button id="cart-button" class="cart-btn">
                            В Корзине (<span id="cart-count"><?= $cartItemCount ?></span>)
                        </button>
                    </div>


    <script>
        function toggleMenu() {
            document.getElementById('mainNav').classList.toggle('menu-open');
        }

        window.addEventListener('scroll', function() {
            document.getElementById('siteHeader').classList.toggle('fixed', window.scrollY > 100);
        });


        // Открыть и закрыть корзину
        document.getElementById('cart-button').addEventListener('click', function() {
            const cartModal = document.getElementById('cart-modal');
            const isVisible = cartModal.style.display === 'block';
            cartModal.style.display = isVisible ? 'none' : 'block'; // Переключаем видимость
            if (!isVisible) {
                loadCartData(); // Загружаем данные только при открытии
            }
        });
    </script>
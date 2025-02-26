<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/resources/css/mobile/header_mobile.css");
Asset::getInstance()->addJs("/resources/js/script.js"); // JS
Asset::getInstance()->addCss("/resources/css/mobile/footer_mobile.css");

// Получаем товары в корзине из сессии
session_start(); // Запуск сессии
$cartItemCount = "<script>document.write(localStorage.getItem('cartItems') ? JSON.parse(localStorage.getItem('cartItems')).reduce((acc, item) => acc + item.quantity, 0) : 0);</script>";
$cartItems = isset($_SESSION['cartItems']['cartItems']) ? $_SESSION['cartItems']['cartItems'] : []; // Получаем массив товаров

// Получаем значение свойства EL_DESCRIPTION элемента инфоблока (ID = 67102, IBLOCK_ID = 3, SECTION_ID = 35)
$phone = "";
$res = CIBlockElement::GetList(
    [],
    [
        "IBLOCK_ID" => 4,  // ID инфоблока
        //"SECTION_ID" => 3, // Раздел
        "ID" => 22371,      // ID элемента
        "ACTIVE" => "Y"     // Только активные элементы
    ],
    false,
    false,
    ["ID", "NAME", "PROPERTY_EL_DESCRIPTION"] // Получаем свойство EL_DESCRIPTION
);

if ($element = $res->Fetch()) {
    // Проверяем, что свойство EL_DESCRIPTION существует и содержит номер телефона
    if (!empty($element["PROPERTY_EL_DESCRIPTION_VALUE"])) {
        $phone = $element["PROPERTY_EL_DESCRIPTION_VALUE"];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="stylesheet" href="/resources/css/mobile/header_mobile.css" media="(max-width: 768px)">
    <link rel="stylesheet" href="/resources/css/header.css" media="(min-width: 769px)">
</head>

<body>
    <div id="panel"><?php $APPLICATION->ShowPanel(); ?></div>
    <header id="siteHeader" class="header">
        <div class="header-content">

            <div class="logo">
                <a href="/"><img src="/resources/img/logo/resource_1.png" alt="ARMA-T.RU"></a>
            </div>

            <div class="contact-container">
                <?php if ($phone): ?>
                    <p><a href="tel:<?= preg_replace('/\D/', '', $phone) ?>" class="phone-link"><?= $phone ?></a></p>
                <?php else: ?>
                    <p><a href="tel:+70000000000" class="phone-link">+7(000) 000-00-00</a></p>
                <?php endif; ?>
                <button class="request-btn">Оставить заявку</button>
            </div>

            <button class="menu-toggle" onclick="toggleMenu()">☰</button>
            <nav id="mobileMenu">
                <ul class="menu-list">
                    <li><a href="#">Главная</a></li>
                    <li><a href="#">Каталог</a></li>
                    <li><a href="#">О компании</a></li>
                    <li><a href="#">Контакты</a></li>
                </ul>
                <form class="nav-search-form" method="GET" action="index.php">
                    <input type="text" id="search" class="full-width-search" placeholder="Поиск...">
                    <div id="suggestions"></div>
                </form>

                <div class="mobile-search">
                    <input type="text" class="search-input" placeholder="Поиск...">
                    <button class="search-btn"> </button>

                    <div id="cart-modal" class="cart-modal">
                        <div class="cart-modal-overlay" id="cart-modal-overlay"></div>
                        <div class="cart-modal-content">
                            <span class="close-btn" id="close-cart-modal">&times;</span>
                            <h2>Корзина</h2>
                            <div id="cart-items" class="cart-items-container"></div>
                            <div class="cart-modal-footer">
                                <button id="clear-cart" class="button">Очистить корзину</button>
                                <button id="checkout" class="button">Оформить заказ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <script type="text/javascript">
    function toggleMenu() {
        const nav = document.getElementById('mobileMenu');
        nav.classList.toggle('open');
    }

    window.addEventListener('scroll', function() {
        document.getElementById('siteHeader').classList.toggle('fixed', window.scrollY > 100);
    });
    </script>

<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/resources/css/header-mobile.css");

// Получаем номер телефона
$phone = "";
$res = CIBlockElement::GetList(
    [],
    [
        "IBLOCK_ID" => 4,
        "ID" => 22371,
        "ACTIVE" => "Y"
    ],
    false,
    false,
    ["ID", "NAME", "PROPERTY_EL_DESCRIPTION"]
);

if ($element = $res->Fetch()) {
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
    <meta name="description" content="Описание страницы для SEO">
    <meta name="keywords" content="Ключевые слова для SEO">
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <!-- Динамическое подключение стилей в зависимости от ширины экрана -->
</head>
<body>
    <div id="panel"><?php $APPLICATION->ShowPanel(); ?></div>
    <header id="siteHeader" class="header">
        <div class="header-content">
            <!-- Логотип -->
            <div class="logo">
                <a href="/"><img src="/resources/img/logo/resource_1.png" alt="ARMA-T.RU"></a>
            </div>

            <!-- Кнопка меню для мобильных устройств -->
            <button class="menu-toggle" onclick="toggleMenu()">&#9776;</button>

            <!-- Контактная информация -->
            <div class="contact-container">
                <?php if ($phone): ?>
                    <p><a href="tel:<?= preg_replace('/\D/', '', $phone) ?>" class="phone-link"><?= $phone ?></a></p>
                <?php else: ?>
                    <p><a href="tel:+70000000000" class="phone-link">+7(000) 000-00-00</a></p>
                <?php endif; ?>
                <button onclick="openForm()">Оставить заявку</button>
            </div>

            <!-- Навигационное меню и поиск -->
            <div class="nav-search">
                <nav id="mainNav">
                    <a href="/">О компании</a>
                    <a href="/catalog/index.php?SECTION_ID=1">Каталог</a>
                    <a href="/#contact">Контакты</a>
                    <a href="/#delivery">Доставка</a>
                    <a href="/#pay">Оплата</a>
                </nav>
                <form class="nav-search-form" method="GET" action="index.php">
                    <input type="text" id="search" class="full-width-search" placeholder="Поиск...">
                    <div id="suggestions"></div>
                </form>
            </div>
        </div>
    </header>

    <script>
        function toggleMenu() {
            const nav = document.getElementById('mainNav');
            const header = document.getElementById('siteHeader');
            nav.classList.toggle('menu-open');
            header.classList.toggle('menu-expanded');
        }

        function openForm() {
            // Логика открытия формы заявки
            alert('Форма заявки');
        }
    </script>
</body>
</html>
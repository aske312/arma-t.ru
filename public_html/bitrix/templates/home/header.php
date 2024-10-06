<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

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
    <div id="panel"> <?php $APPLICATION->ShowPanel(); ?> </div>
    <header id="siteHeader">
        <div class="header-content">
            <div class="logo">
                <a href="/"><img src="/local/img/logo/resource_1.png" alt="My Logo"></a>
            </div>
            <div class="nav-search">
                <button class="menu-toggle" onclick="toggleMenu()">&#9776;</button>
                <nav id="mainNav">
                    <a href="/#Company">О компании</a>
                    <a href="/catalog/">Каталог</a>
                    <a href="/#Contact">Контакты</a>
                    <a href="/#Delivery">Доставка</a>
                    <a href="/#Cash">Оплата</a>
                </nav>
            </div>
            <div class="contact-info">
                <p><a href="tel:+70000000000" class="phone-link">+7 (000) 000-00-00</a></p>
                <button onclick="window.location.href='#Cash'">Оставить заявку</button>

                <!-- Корзина -->
                <?php if ($cartItemCount > 0): // Проверка, есть ли товары в корзине ?>
                <div class="basket-container">
                    <button class="basket-toggle" onclick="toggleBasket()">Корзина (<?php echo $cartItemCount; ?>)</button>
                    <div class="basket-dropdown" id="basketDropdown">
                        <table>
                            <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Артикул</th>
                                <th>Количество</th>
                                <th>Цена</th>
                                <th>Сумма</th>
                            </tr>
                            </thead>
                            <tbody id="basket-items">
                                <!-- Товары будут добавлены динамически -->
                            </tbody>
                        </table>
                        <p>Итоговая стоимость: <span id="total-price">0</span> руб.</p>
                        <button id="checkout">Оформить заказ</button>
                        <button id="clear-cart">Очистить корзину</button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

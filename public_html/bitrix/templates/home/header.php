<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Подключение модулей и стилей
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/header.css");
Asset::getInstance()->addCss("/local/css/footer.css");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Описание страницы для SEO">
    <meta name="keywords" content="Ключевые слова для SEO">
    <?$APPLICATION->ShowHead();?>
    <title><?$APPLICATION->ShowTitle();?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script> <!-- cookie -->
</head>
    <body>
        <div id="panel"> <?$APPLICATION->ShowPanel();?> </div>
        <header id="siteHeader">
            <div class="header-content">
                <div class="logo">
                    <a href="/"><img src="/local/img/logo/resource_1.png" alt="My Logo"></a>
                </div>
                <div class="nav-search">
                    <button class="menu-toggle" onclick="toggleMenu()">&#9776;</button>
                    <nav id="mainNav">
                        <a href="/#Company">О компании</a>
                        <a href="/catalog/index.php?SECTION_ID=2">Каталог</a>
                        <a href="/#Contact">Контакты</a>
                        <a href="/#Delivery">Доставка</a>
                        <a href="/#Cash">Оплата</a>
                    </nav>
                    <!--
                        <?php $APPLICATION->IncludeComponent(
                            "search",
                            "",
                            Array(
                                "ACTION_VARIABLE" => "action",
                                "AJAX_MODE" => "N",
                                "AJAX_OPTION_ADDITIONAL" => "",
                                "AJAX_OPTION_HISTORY" => "N",
                                "AJAX_OPTION_JUMP" => "N",
                                "AJAX_OPTION_STYLE" => "Y",
                                "BASKET_URL" => "/personal/basket.php",
                                "CACHE_TIME" => "36000000",
                                "CACHE_TYPE" => "A",
                                "CHECK_DATES" => "N",
                                "DETAIL_URL" => "",
                                "DISPLAY_BOTTOM_PAGER" => "Y",
                                "DISPLAY_COMPARE" => "N",
                                "DISPLAY_TOP_PAGER" => "N",
                                "ELEMENT_SORT_FIELD" => "sort",
                                "ELEMENT_SORT_FIELD2" => "id",
                                "ELEMENT_SORT_ORDER" => "asc",
                                "ELEMENT_SORT_ORDER2" => "desc",
                                "IBLOCK_ID" => "",
                                "IBLOCK_TYPE" => "catalog",
                                "LINE_ELEMENT_COUNT" => "3",
                                "NO_WORD_LOGIC" => "N",
                                "OFFERS_LIMIT" => "5",
                                "PAGER_DESC_NUMBERING" => "N",
                                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                "PAGER_SHOW_ALL" => "N",
                                "PAGER_SHOW_ALWAYS" => "N",
                                "PAGER_TEMPLATE" => ".default",
                                "PAGER_TITLE" => "Товары",
                                "PAGE_ELEMENT_COUNT" => "30",
                                "PRICE_CODE" => array(),
                                "PRICE_VAT_INCLUDE" => "Y",
                                "PRODUCT_ID_VARIABLE" => "id",
                                "PRODUCT_PROPERTIES" => array(),
                                "PRODUCT_PROPS_VARIABLE" => "prop",
                                "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                                "PROPERTY_CODE" => array("",""),
                                "RESTART" => "N",
                                "SECTION_ID_VARIABLE" => "SECTION_ID",
                                "SECTION_URL" => "",
                                "SHOW_PRICE_COUNT" => "1",
                                "USE_LANGUAGE_GUESS" => "Y",
                                "USE_PRICE_COUNT" => "N",
                                "USE_PRODUCT_QUANTITY" => "N",
                                "USE_SEARCH_RESULT_ORDER" => "N",
                                "USE_TITLE_RANK" => "N"
                            )
                        );?> -->
                </div>
                <div class="contact-info">
                    <p><a href="tel:+70000000000" class="phone-link">+7 (000) 000-00-00</a></p>
                    <button onclick="window.location.href='#Cash'">Оставить заявку</button>

                    <div id="cart-popup" style="display:none;">
                        <h2>Корзина</h2>
                        <table>
                            <?php
                            $cart = json_decode($_COOKIE['cart'] ?? '[]', true);
                            foreach ($cart as $id => $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= htmlspecialchars($item['price']) ?> руб.</td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="remove_from_cart" value="<?= $id ?>">
                                            <button type="submit">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                </div>
            </div>
        </header>
        <script>
            // Функции плавующего меню
            function toggleMenu() {
                var nav = document.getElementById('mainNav');
                nav.classList.toggle('menu-open');
            }

            window.onscroll = function() { stickyHeader() };

            var header = document.getElementById("siteHeader");
            var sticky = header.offsetTop;

            function stickyHeader() {
                if (window.pageYOffset > sticky) {
                    header.classList.add("fixed");
                } else {
                    header.classList.remove("fixed");
                }
            }

            //**** КОРЗИНА ****//
            // Функция для управления открытием/закрытием корзины
            document.getElementById('toggle-cart').addEventListener('click', function () {
                var cartPopup = document.getElementById('cart-popup');
                cartPopup.classList.toggle('hidden'); // Показать/скрыть корзину при нажатии на кнопку
                loadCartItems(); // Загружаем товары в корзину при открытии
            });

            // Функция загрузки товаров в корзину
            function loadCartItems() {
                var cart = JSON.parse(getCookie('cart') || '[]');
                var cartItemsContainer = document.getElementById('cart-items');
                cartItemsContainer.innerHTML = '';
                var totalPrice = 0;

                if (cart.length === 0) {
                    cartItemsContainer.innerHTML = '<p>В корзине нет товаров.</p>';
                    document.getElementById('total-price').innerText = 'Итоговая стоимость: 0 руб.';
                    return; // Выход, если корзина пустая
                }

                cart.forEach(function(item) {
                    // Создание HTML для каждого товара
                    var itemElement = document.createElement('div');
                    itemElement.classList.add('cart-item'); // Добавить класс для стилизации
                    itemElement.innerHTML = `
                        <div>${item.name}</div>
                        <div>${item.price} руб.</div>
                        <button class="remove-item" data-id="${item.id}">Удалить</button>
                    `;
                    cartItemsContainer.appendChild(itemElement);
                    totalPrice += item.price; // Суммируем общую стоимость
                });

                document.getElementById('total-price').innerText = 'Итоговая стоимость: ' + totalPrice + ' руб.';

                // Обработка удаления товара
                var removeButtons = document.querySelectorAll('.remove-item');
                removeButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        var itemId = button.getAttribute('data-id');
                        removeItemFromCart(itemId);
                        loadCartItems(); // Обновляем корзину после удаления
                    });
                });
            }

            // Функция удаления товара из корзины
            function removeItemFromCart(itemId) {
                var cart = JSON.parse(getCookie('cart') || '[]');
                cart = cart.filter(function(item) {
                    return item.id !== itemId; // Удаляем товар с указанным ID
                });
                setCookie('cartItems', JSON.stringify(cart), 7); // Сохраняем обновленную корзину
            }

            // Функции для работы с cookie
            function setCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }

            function getCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            function eraseCookie(name) {
                document.cookie = name + '=; Max-Age=-99999999;';
            }
        </script>
<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Подключение модулей и стилей
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/catalog.css");
Asset::getInstance()->addCss("/local/css/header.css");
Asset::getInstance()->addCss("/local/css/footer.css");
Asset::getInstance()->addJs("/local/js/script.js"); // не работает
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
    <script src="../local/js/script.js"></script>
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
                        <a href="/catalog/">Каталог</a>
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

                    <div id="cart">
                        <button class="cart-breaks" id="toggle-cart">Корзина (<span id="cart-counter">0</span>)</button>
                        <div id="cart-popup" class="hidden">
                            <h3>Корзина</h3>
                            <div id="cart-items"></div>
                            <p id="total-price">Итоговая стоимость: 0</p>
                            <button id="clear-cart">Очистить корзину</button>
                            <button id="checkout">Оформить заказ</button>
                        </div>
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

            window.onscroll = function() {stickyHeader()};

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
            document.getElementById('toggle-cart').addEventListener('click', function () {
                var cartPopup = document.getElementById('cart-popup');
                cartPopup.classList.toggle('hidden'); // Переключаем видимость корзины
                loadCartItems(); // Загружаем товары в корзину при открытии
            });

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

                var promises = cart.map(function (productId) {
                    return getProductDataById(productId).then(function (productData) {
                        return { productId, productData };
                    });
                });

                Promise.all(promises).then(function (results) {
                    results.forEach(function (item, index) {
                        var productId = item.productId;
                        var productData = item.productData;

                        if (productData.error) return; // Игнорируем ошибки

                        var quantity = cart.filter(id => id === productId).length; // Подсчет количества одного товара
                        var itemTotal = productData.price * quantity; // Сумма за товар
                        totalPrice += itemTotal;

                        var itemDiv = document.createElement('div');
                        itemDiv.innerHTML = `
                            <img src="${productData.image}" alt="${productData.name}" style="width: 50px; height: 50px;">
                            <span>${index + 1}. ${productData.name} (x${quantity}) - ${productData.price} руб. <strong>${itemTotal} руб.</strong></span>
                            <button class="remove-item" data-id="${productId}">×</button>
                        `;
                        cartItemsContainer.appendChild(itemDiv);
                    });

                    document.getElementById('total-price').innerText = 'Итоговая стоимость: ' + totalPrice + ' руб.';

                    // Обработка удаления товара
                    document.querySelectorAll('.remove-item').forEach(function (button) {
                        button.addEventListener('click', function () {
                            removeItemFromCart(this.getAttribute('data-id'));
                        });
                    });
                });
            }

            function removeItemFromCart(productId) {
                var cart = JSON.parse(getCookie('cart') || '[]');
                cart = cart.filter(id => id !== productId); // Удаление товара из массива
                setCookie('cart', JSON.stringify(cart), 7); // Сохраняем куки
                loadCartItems(); // Обновляем список товаров в корзине
                updateCartCounter(); // Обновляем счетчик
            }

            document.getElementById('clear-cart').addEventListener('click', function () {
                setCookie('cart', JSON.stringify([]), 7); // Очищаем куки
                loadCartItems(); // Обновляем список товаров в корзине
                updateCartCounter(); // Обновляем счетчик
            });

            document.getElementById('checkout').addEventListener('click', function () {
                window.location.href = '/checkout/'; // Переход на страницу оформления заказа
            });

            function getProductDataById(productId) {
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'get_product_data.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            resolve(JSON.parse(xhr.responseText));
                        }
                    };
                    xhr.send('id=' + productId);
                });
            }
        </script>
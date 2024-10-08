<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Подключение модулей и стилей
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/header.css");
Asset::getInstance()->addCss("/local/css/footer.css");

// Получаем количество товаров в корзине из куки
$cartItems = isset($_COOKIE['cartItems']) ? json_decode($_COOKIE['cartItems'], true) : [];
$cartItemCount = 0;
foreach ($cartItems as $item) {
    $cartItemCount += $item['quantity'];
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

                    <div class="header-content">
                        <div class="cart">
                            <button id="basket-button">
                                В корзине <span id="cart-counter"><?= $cartItemCount ?></span> позиций
                            </button>
                        </div>
                    </div>

                    <!-- Модальное окно корзины -->
                    <div id="basket-popup" class="hidden">
                        <div class="basket-content">
                            <table id="basket-items">
                                <!-- Здесь будет динамически генерироваться список товаров -->
                            </table>
                            <div>
                                <strong>Итого:</strong> <span id="total-price">0</span> руб.
                            </div>
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
            // Открытие и закрытие корзины по клику
            document.getElementById('basket-button').addEventListener('click', function () {
                var cartPopup = document.getElementById('basket-popup');
                cartPopup.classList.toggle('hidden');
                loadCartItems();
            });

            // Загрузка товаров из куки и отображение в корзине
            function loadCartItems() {
                var cartItems = getCartItemsFromCookies();
                var productIds = cartItems.map(item => item.id);

                if (productIds.length > 0) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'get_product_data.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            var basketData = JSON.parse(xhr.responseText);
                            updateCart(basketData);
                        }
                    };
                    xhr.send('productIds=' + JSON.stringify(productIds));
                }
            }

            // Очистка корзины
            document.getElementById('clear-cart').addEventListener('click', function () {
                Cookies.remove('cartItems');
                updateCart([]); // Очищаем корзину на экране
                updateCartCounter();
            });
        </script>
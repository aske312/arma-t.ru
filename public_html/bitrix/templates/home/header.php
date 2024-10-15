<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Подключение модулей и стилей
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/header.css");
Asset::getInstance()->addCss("/local/css/footer.css");

// Получаем количество товаров в корзине из куки
$cartItems = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
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

                    <div class="cart-wrapper">

                        <!-- Корзина -->
                        <div class="cart-icon">
                            <button id="cart-button" class="cart-btn">
                                В Корзине (<span id="cart-count">0</span>)
                            </button>
                        </div>

                        <!-- Модальное окно с товарами корзины -->
                        <div id="cart-modal" class="cart-modal" style="display: none;">
                            <div class="cart-modal-content">
                                <span class="close-btn" id="close-cart-modal">&times;</span>
                                <h2>Товары в корзине</h2>
                                <div id="cart-items">
                                    <!-- Здесь будут отображаться товары из корзины -->
                                </div>
                                <div id="cart-total">

                                    <!-- Здесь будет отображаться итоговая сумма -->
                                </div>
                                <button id="clear-cart">Очистить корзину</button>
                                <button id="checkout">Оформить заказ</button>
                            </div>
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
            // Обработчик для открытия модального окна корзины
            document.getElementById('cart-button').addEventListener('click', function() {
                var cartModal = document.getElementById('cart-modal');
                cartModal.style.display = 'block';
                loadCartData();
            });

            // Обработчик для закрытия модального окна корзины
            document.getElementById('close-cart-modal').addEventListener('click', function() {
                document.getElementById('cart-modal').style.display = 'none';
            });

            // Функция загрузки товаров из куки в модальное окно корзины
            function loadCartData() {
                var cartItems = getCartItemsFromCookie();

                if (cartItems.length > 0) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '/path/to/get_product_data.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (Array.isArray(response)) {
                                updateCart(response);  // Функция обновления содержимого корзины
                            }
                        }
                    };
                    xhr.send('cartItems=' + encodeURIComponent(JSON.stringify(cartItems)));
                } else {
                    document.getElementById('cart-items').innerHTML = '<p>Ваша корзина пуста</p>';
                }
            }

//            // Загрузка товаров из куки и отображение в корзине
//            function loadCartItems() {
//                var cartItems = getCartItemsFromCookies();
//                var productIds = cartItems.map(item => item.id);
//
//                if (productIds.length > 0) {
//                    var xhr = new XMLHttpRequest();
//                    xhr.open('POST', 'get_product_data.php', true);
//                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
//                    xhr.onreadystatechange = function () {
//                        if (xhr.readyState === 4 && xhr.status === 200) {
//                            var basketData = JSON.parse(xhr.responseText);
//                            updateCart(basketData);
//                        }
//                    };
//                    xhr.send('productIds=' + JSON.stringify(productIds));
//                }
//            }

//            // Очистка корзины
//            document.getElementById('clear-cart').addEventListener('click', function () {
//                Cookies.remove('cartItems');
//                updateCart([]); // Очищаем корзину на экране
//                updateCartCounter();
//            });

            // Функция обновления содержимого корзины
            function updateCart(basketData) {
                var cartItemsContainer = document.getElementById('cart-items');
                cartItemsContainer.innerHTML = '';
                var totalSum = 0;

                basketData.forEach(function(item, index) {
                    var itemRow = document.createElement('div');
                    itemRow.className = 'cart-item';
                    itemRow.innerHTML = `
                        <div>№${index + 1}</div>
                        <div>Название: ${item.name}</div>
                        <div>Цена: ${item.price}</div>
                        <div>Количество: ${item.quantity}</div>
                        <div>Сумма: ${item.total}</div>
                        <button class="remove-item-btn" data-id="${item.id}">Удалить</button>
                    `;
                    cartItemsContainer.appendChild(itemRow);

                    totalSum += item.total;
                });

                document.getElementById('cart-total').innerHTML = `Общая сумма: ${totalSum} руб.`;

                // Обработчик удаления товара из корзины
                document.querySelectorAll('.remove-item-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var productId = this.getAttribute('data-id');
                        removeCartItem(productId);
                    });
                });
            }

            // Функция получения товаров из куки
            function getCartItemsFromCookie() {
                var cartItems = [];
                var cookies = document.cookie.split(';');

                cookies.forEach(function(cookie) {
                    var cookiePair = cookie.split('=');
                    if (cookiePair[0].trim() === 'cartItems') {
                        cartItems = JSON.parse(decodeURIComponent(cookiePair[1]));
                    }
                });

                return cartItems;
            }

            // Функция удаления товара из корзины
            function removeCartItem(productId) {
                var cartItems = getCartItemsFromCookie();
                cartItems = cartItems.filter(function(item) {
                    return item.id !== productId;
                });
                document.cookie = 'cartItems=' + encodeURIComponent(JSON.stringify(cartItems)) + ';path=/';
                loadCartData();  // Обновляем корзину
            }

            // Обработчик очистки корзины
            document.getElementById('clear-cart').addEventListener('click', function() {
                document.cookie = 'cartItems=; Max-Age=-99999999;';  // Очищаем куки
                loadCartData();  // Обновляем корзину
            });

            // Обработчик оформления заказа
            document.getElementById('checkout').addEventListener('click', function() {
                window.location.href = '/checkout/';  // Переход на страницу оформления заказа
            });

//                function updateQuantity(productId, quantity) {
//                    var cartItems = getCartItemsFromCookies();
//                    cartItems = cartItems.map(function (item) {
//                        if (item.id === productId) {
//                            item.quantity = parseInt(quantity);
//                        }
//                        return item;
//                    });
//                    saveCartItemsToCookies(cartItems);
//                    loadCartItems(); // Обновляем данные в корзине
//                }
//
//                function removeFromCart(productId) {
//                    var cartItems = getCartItemsFromCookies();
//                    cartItems = cartItems.filter(function (item) {
//                        return item.id !== productId;
//                    });
//                    saveCartItemsToCookies(cartItems);
//                    loadCartItems(); // Обновляем данные в корзине
//                    updateCartCounter();
//                }
//
//                function getCookie(name) {
//                    var value = "; " + document.cookie;
//                    var parts = value.split("; " + name + "=");
//                    if (parts.length == 2) return parts.pop().split(";").shift();
//                }
//
//                function parseCartCookie() {
//                    var cartCookie = getCookie('cart');
//                    if (cartCookie) {
//                        // Декодирование URL и парсинг JSON
//                        try {
//                            return JSON.parse(decodeURIComponent(cartCookie));
//                        } catch (error) {
//                            console.error("Ошибка при парсинге куки корзины:", error);
//                            return [];
//                        }
//                    }
//                    return [];
//                }
//
//                var cartItems = parseCartCookie();
//                console.log(cartItems); // Должен вывести массив с объектами товаров
        </script>
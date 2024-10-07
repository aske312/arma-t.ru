<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Подключение модулей и стилей
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/catalog.css");
Asset::getInstance()->addCss("/local/css/header.css");
Asset::getInstance()->addCss("/local/css/footer.css");
Asset::getInstance()->addJs("/local/js/script.js");

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

                    <!-- Кнопка для корзины со счетчиком -->
                    <button id="cart-button" class="hidden">
                        Корзина (<span id="cart-count">0</span>)
                    </button>

                    <!-- Выпадающий список корзины -->
                    <div id="cart-dropdown" class="cart-dropdown hidden">
                        <h3>Ваша корзина</h3>
                        <ul id="cart-items">
                            <!-- Список товаров будет добавляться динамически -->
                        </ul>
                        <p>Итоговая стоимость: <span id="cart-total-price">0</span> руб.</p>
                        <button id="checkout">Оформить заказ</button>
                    </div>

                </div>
            </div>
        </header>

        <script>
            // Функция для добавления товара в корзину
            function addToCart(productId, productArticle, productPrice, productName, productImage) {
                let cart = getCartFromCookies();

                // Проверяем, есть ли товар в корзине
                let item = cart.find(item => item.id === productId);
                if (item) {
                    // Увеличиваем количество, если товар уже есть
                    item.quantity++;
                } else {
                    // Добавляем новый товар
                    cart.push({
                        id: productId,
                        article: productArticle,
                        price: productPrice || 0, // Если цена 0 или отсутствует
                        name: productName,
                        image: productImage || 'default-image.jpg', // Если нет картинки
                        quantity: 1
                    });
                }

                // Сохраняем корзину в куки
                saveCartToCookies(cart);

                // Обновляем отображение корзины
                updateCartUI();
            }

            // Получение корзины из куки
            function getCartFromCookies() {
                let cart = document.cookie.split('; ').find(row => row.startsWith('cart='));
                return cart ? JSON.parse(decodeURIComponent(cart.split('=')[1])) : [];
            }

            // Сохранение корзины в куки
            function saveCartToCookies(cart) {
                document.cookie = 'cart=' + encodeURIComponent(JSON.stringify(cart)) + '; path=/; max-age=31536000'; // 1 год
            }

            // Обновление UI корзины
            function updateCartUI() {
                let cart = getCartFromCookies();
                let cartCount = cart.reduce((count, item) => count + item.quantity, 0);
                let cartButton = document.getElementById('cart-button');
                let cartTotalPrice = cart.reduce((total, item) => total + (item.price * item.quantity), 0);

                if (cartCount > 0) {
                    cartButton.classList.remove('hidden');
                    document.getElementById('cart-count').textContent = cartCount;
                    document.getElementById('cart-total-price').textContent = cartTotalPrice;
                } else {
                    cartButton.classList.add('hidden');
                }
            }

            // Отображение товаров в выпадающем списке корзины
            function showCartItems() {
                let cart = getCartFromCookies();
                let cartItems = document.getElementById('cart-items');
                cartItems.innerHTML = '';

                cart.forEach(item => {
                    let listItem = document.createElement('li');
                    listItem.innerHTML = `
                        <img src="${item.image}" alt="${item.name}" width="50" />
                        <span>${item.name}</span> x${item.quantity} - ${item.price * item.quantity} руб.
                    `;
                    cartItems.appendChild(listItem);
                });
            }

            // Показ/скрытие выпадающего списка корзины
            document.getElementById('cart-button').addEventListener('click', function () {
                let cartDropdown = document.getElementById('cart-dropdown');
                cartDropdown.classList.toggle('hidden');
                showCartItems();
            });

            // Пример кнопки добавления товара в корзину
            document.querySelectorAll('.catalog-item-add-to-cart').forEach(function (button) {
                button.addEventListener('click', function () {
                    let productId = this.getAttribute('data-id');
                    let productArticle = this.getAttribute('data-article');
                    let productPrice = this.getAttribute('data-price');
                    let productName = this.getAttribute('data-name');
                    let productImage = this.getAttribute('data-image');

                    addToCart(productId, productArticle, productPrice, productName, productImage);
                });
            });

            // Инициализация корзины при загрузке страницы
            document.addEventListener('DOMContentLoaded', updateCartUI);



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
        </script>
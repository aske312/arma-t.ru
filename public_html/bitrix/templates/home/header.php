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

// Подсчитываем общее количество товаров в корзине
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
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script> <!-- Подключаем библиотеку для работы с куками -->
</head>
<body>
    <div id="panel"><?php $APPLICATION->ShowPanel(); ?></div>
    <header id="siteHeader" class="header">
        <div class="header-content">
            <div class="logo">
                <a href="/"><img src="/local/img/logo/resource_1.png" alt="My Logo"></a>
            </div>
            <div class="nav-search">
                <button class="menu-toggle" onclick="toggleMenu()">&#9776;</button>
                <nav id="mainNav">
                    <a href="/">О компании</a>
                    <a href="/catalog/index.php?SECTION_ID=2">Каталог</a>
                    <a href="/contact/">Контакты</a>
                    <a href="/delivery/">Доставка</a>
                    <a href="/payment/">Оплата</a>
                </nav>
            </div>
            <div class="contact-info">
                <p><a href="tel:+70000000000" class="phone-link">+7 (000) 000-00-00</a></p>
                <button onclick="window.location.href='#Cash'">Оставить заявку</button>

                <div class="cart-wrapper">
                    <!-- Кнопка Корзины -->
                    <div class="cart-icon">
                        <button id="cart-button" class="cart-btn">
                            В Корзине (<span id="cart-count"><?= $cartItemCount ?></span>)
                        </button>
                    </div>

                    <!-- Модальное окно с товарами корзины -->
                    <div id="cart-modal" class="cart-modal">
                        <div class="cart-modal-content">
                            <span class="close-btn" id="close-cart-modal">&times;</span>
                            <h2>Товары в корзине</h2>

                            <!-- Таблица товаров в корзине -->
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Цена за ед.</th>
                                        <th>Количество</th>
                                        <th>Общая цена</th>
                                        <th></th> <!-- For the delete button -->
                                    </tr>
                                </thead>
                                <tbody id="cart-items">
                                    <!-- Динамически обновляемый контент -->
                                </tbody>
                            </table>

                            <div id="cart-total">
                                <!-- Итоговая сумма будет отображаться здесь -->
                            </div>
                            <button id="clear-cart" class="button">Очистить корзину</button>
                            <button id="checkout" class="button">Оформить заказ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Функции для плавающего меню
        function toggleMenu() {
            var nav = document.getElementById('mainNav');
            nav.classList.toggle('menu-open');
        }

        window.addEventListener('scroll', function() {
            const header = document.getElementById('siteHeader');
            if (window.scrollY > 100) {  // Замените 100 на значение, когда фиксировать
                header.classList.add('fixed');
            } else {
                header.classList.remove('fixed');
            }
        });

        // Обновление количества товаров в корзине
        function updateCartCount() {
            var cartItems = getCartItemsFromCookie();
            var count = cartItems.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        // Получение товаров из куки
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

        // Открытие/закрытие модального окна корзины
        document.getElementById('cart-button').addEventListener('click', function() {
            var cartModal = document.getElementById('cart-modal');
            cartModal.style.display = (cartModal.style.display === 'block') ? 'none' : 'block';
            loadCartData(); // Загружаем данные при открытии корзины
        });

        document.getElementById('close-cart-modal').addEventListener('click', function() {
            document.getElementById('cart-modal').style.display = 'none';
        });

        // Загрузка товаров из куки в таблицу корзины
        function loadCartData() {
            var cartItems = getCartItemsFromCookie();
            var cartItemsContainer = document.getElementById('cart-items');
            cartItemsContainer.innerHTML = '';
            var totalSum = 0;

            if (cartItems.length > 0) {
                cartItems.forEach(function(item) {
                    var truncatedName = item.name.length > 20 ? item.name.substring(0, 20) + '...' : item.name;

                    var row = `
                        <tr>
                            <td title="${item.name}">${truncatedName}</td>
                            <td>${item.price} руб.</td>
                            <td>
                                <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                                <input type="text" class="quantity" value="${item.quantity}" readonly>
                                <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                            </td>
                            <td>${item.total} руб.</td>
                            <td><button class="remove-item-btn" data-id="${item.id}">&times;</button></td>
                        </tr>
                    `;
                    cartItemsContainer.innerHTML += row;
                    totalSum += item.total;
                });

                document.getElementById('cart-total').innerHTML = `Общая сумма: ${totalSum} руб.`;
            } else {
                cartItemsContainer.innerHTML = '<tr><td colspan="5">Ваша корзина пуста</td></tr>';
            }

            updateCartCount();
        }

        // Обновление количества товара
        function updateQuantity(productId, change) {
            var cartItems = getCartItemsFromCookie();
            var item = cartItems.find(item => item.id === productId);
            if (item) {
                item.quantity += change;
                if (item.quantity < 1) {
                    removeCartItem(productId);
                } else {
                    document.cookie = 'cartItems=' + encodeURIComponent(JSON.stringify(cartItems)) + ';path=/';
                    loadCartData();
                }
            }
        }

        // Удаление товара
        function removeCartItem(productId) {
            var cartItems = getCartItemsFromCookie();
            cartItems = cartItems.filter(item => item.id !== productId);
            document.cookie = 'cartItems=' + encodeURIComponent(JSON.stringify(cartItems)) + ';path=/';
            loadCartData();
        }

        // Очистка корзины
        document.getElementById('clear-cart').addEventListener('click', function() {
            document.cookie = 'cartItems=; Max-Age=-99999999; path=/';  // Очищаем куки
            loadCartData();
        });

        // Переход к оформлению заказа
        document.getElementById('checkout').addEventListener('click', function() {
            window.location.href = '/checkout/';
        });

        // Инициализация
        updateCartCount();
    </script>
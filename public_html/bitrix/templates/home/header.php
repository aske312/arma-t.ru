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
    <?$APPLICATION->ShowHead();?>
    <title><?$APPLICATION->ShowTitle();?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script> <!-- Подключаем библиотеку для работы с куками -->
</head>
<body>
    <div id="panel"><?$APPLICATION->ShowPanel();?></div>
    <header id="siteHeader">
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
                            <div id="cart-items">
                                <!-- Список товаров будет отображаться здесь -->
                            </div>
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
        // Функции для плавующего меню
        function toggleMenu() {
            var nav = document.getElementById('mainNav');
            nav.classList.toggle('menu-open');
        }

        // Проверка и обновление счетчика корзины
        function updateCartCount() {
            var cartItems = getCartItemsFromCookie();
            var count = cartItems.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        // Проверка наличия товаров в куки
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

        // Обработчик для открытия и закрытия модального окна корзины
        document.getElementById('cart-button').addEventListener('click', function() {
            var cartModal = document.getElementById('cart-modal');
            cartModal.style.display = (cartModal.style.display === 'block') ? 'none' : 'block';
            loadCartData(); // Загрузка данных корзины при открытии
        });

        // Закрытие модального окна корзины по крестику
        document.getElementById('close-cart-modal').addEventListener('click', function() {
            document.getElementById('cart-modal').style.display = 'none';
        });

        // Функция загрузки товаров из куки в модальное окно корзины
        function loadCartData() {
            var cartItems = getCartItemsFromCookie();
            if (cartItems.length > 0) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/catalog/get_product_data.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        updateCart(response);
                    }
                };
                xhr.send('cartItems=' + encodeURIComponent(JSON.stringify(cartItems)));
            } else {
                document.getElementById('cart-items').innerHTML = '<p>Ваша корзина пуста</p>';
            }
        }

        // Функция обновления содержимого корзины
        function updateCart(basketData) {
            var cartItemsContainer = document.getElementById('cart-items');
            cartItemsContainer.innerHTML = '';
            var totalSum = 0;

            // Создание заголовка таблицы
            var tableHeader = `
                <div class="cart-table-header">
                    <div>Наименование</div>
                    <div>Цена за ед.</div>
                    <div>Количество</div>
                    <div>Общая цена</div>
                </div>
            `;
            cartItemsContainer.innerHTML += tableHeader;

            basketData.forEach(function(item, index) {
                var truncatedName = item.name.length > 15 ? item.name.substring(0, 15) + '...' : item.name;

                var itemRow = document.createElement('div');
                itemRow.className = 'cart-item';
                itemRow.innerHTML = `
                    <div title="${item.name}">${truncatedName}</div>
                    <div>${item.price} руб.</div>
                    <div class="quantity-container">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                    </div>
                    <div>${item.total} руб.</div>
                    <button class="remove-item-btn" data-id="${item.id}">&times;</button>
                `;
                cartItemsContainer.appendChild(itemRow);
                totalSum += item.total;
            });

            document.getElementById('cart-total').innerHTML = `Общая сумма: ${totalSum} руб.`;
            updateCartCount(); // Обновляем счетчик после обновления корзины

            // Обработчик удаления товара из корзины
            document.querySelectorAll('.remove-item-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    var productId = this.getAttribute('data-id');
                    removeCartItem(productId);
                });
            });
        }

        // Функция обновления количества товара
        function updateQuantity(productId, change) {
            var cartItems = getCartItemsFromCookie();
            var item = cartItems.find(item => item.id === productId);
            if (item) {
                item.quantity += change;
                if (item.quantity < 1) {
                    removeCartItem(productId);
                } else {
                    document.cookie = 'cartItems=' + encodeURIComponent(JSON.stringify(cartItems)) + ';path=/';
                    loadCartData(); // Обновляем корзину
                }
            }
        }

        // Функция удаления товара из корзины
        function removeCartItem(productId) {
            var cartItems = getCartItemsFromCookie();
            cartItems = cartItems.filter(function(item) {
                return item.id !== productId;
            });
            document.cookie = 'cartItems=' + encodeURIComponent(JSON.stringify(cartItems)) + ';path=/';
            loadCartData();  // Обновляем корзину
            updateCartCount(); // Обновляем счетчик
        }

        // Обработчик очистки корзины
        document.getElementById('clear-cart').addEventListener('click', function() {
            document.cookie = 'cartItems=; Max-Age=-99999999;';  // Очищаем куки
            loadCartData();  // Обновляем корзину
            updateCartCount(); // Обновляем счетчик
        });

        // Обработчик оформления заказа
        document.getElementById('checkout').addEventListener('click', function() {
            window.location.href = '/checkout/';  // Переход на страницу оформления заказа
        });

        // Начальная проверка на наличие товаров в куки
        updateCartCount();
    </script>
<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

session_start(); // Запуск сессии
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/header.css");
Asset::getInstance()->addCss("/local/css/footer.css");

// Получаем товары в корзине из сессии
$cartItems = isset($_SESSION['cartItems']) ? $_SESSION['cartItems']['cartItems'] : []; // Получаем массив товаров
$cartItemCount = array_sum(array_column($cartItems, 'quantity'));
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
                    <div class="cart-icon">
                        <button id="cart-button" class="cart-btn">
                            В Корзине (<span id="cart-count"><?= $cartItemCount ?></span>)
                        </button>
                    </div>

                    <div id="cart-modal" class="cart-modal">
                        <div class="cart-modal-content">
                            <span class="close-btn" id="close-cart-modal">&times;</span>
                            <h2>Товары в корзине</h2>
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Цена за ед.</th>
                                        <th>Количество</th>
                                        <th>Общая цена</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items"></tbody>
                            </table>
                            <div id="cart-total"></div>
                            <button id="clear-cart" class="button">Очистить корзину</button>
                            <button id="checkout" class="button">Оформить заказ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        function toggleMenu() {
            document.getElementById('mainNav').classList.toggle('menu-open');
        }

        window.addEventListener('scroll', function() {
            document.getElementById('siteHeader').classList.toggle('fixed', window.scrollY > 100);
        });

        // Получаем данные корзины из сессии
        function getCartItemsFromSession() {
            return <?= json_encode($cartItems) ?>;
        }

        // Обновление количества товаров в корзине
        function updateCartCount() {
            const cartItems = getCartItemsFromSession();
            const count = cartItems.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        // Загружаем и отображаем данные корзины в модальном окне
        function loadCartData() {
            const cartItems = getCartItemsFromSession();
            const cartItemsContainer = document.getElementById('cart-items');
            cartItemsContainer.innerHTML = '';
            let totalSum = 0;

            if (cartItems.length === 0) {
                cartItemsContainer.innerHTML = '<tr><td colspan="5">Корзина пуста</td></tr>';
                document.getElementById('cart-total').innerText = 'Общая сумма: 0.00 руб.';
                return;
            }

            cartItems.forEach(item => {
                const row = `
                    <tr>
                        <td title="${item.name}">${item.name}</td>
                        <td>${item.price} руб.</td>
                        <td>
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">&#8722;</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" onchange="updateQuantityManual(${item.id}, this.value)">
                            <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">&#43;</button>
                        </td>
                        <td>${(item.price * item.quantity).toFixed(2)} руб.</td>
                        <td><button class="remove-item-btn" onclick="removeCartItem(${item.id})">&#10005;</button></td>
                    </tr>
                `;
                cartItemsContainer.innerHTML += row;
                totalSum += item.price * item.quantity;
            });

            document.getElementById('cart-total').innerText = `Общая сумма: ${totalSum.toFixed(2)} руб.`;
        }

        // Увеличение количества товара
        function updateQuantity(productId, delta) {
            let cartItems = getCartItemsFromSession();
            const item = cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity += delta;
                if (item.quantity < 1) {
                    removeCartItem(productId);
                } else {
                    setCartItemsToSession(cartItems);
                    loadCartData();
                    updateCartCount();
                }
            }
        }

        // Сохранение данных корзины в сессию
        function setCartItemsToSession(cartItems) {
            console.log('Sending cart items to session:', cartItems); // Добавьте эту строку
            fetch('/catalog/update_cart_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cartItems })
            });
        }

        // Ручной ввод количества
        function updateQuantityManual(productId, value) {
            let cartItems = getCartItemsFromSession();
            const item = cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity = Math.max(1, parseInt(value) || 1);
                setCartItemsToSession(cartItems);
                loadCartData();
                updateCartCount();
            }
        }

        // Удаление товара
        function removeCartItem(productId) {
            let cartItems = getCartItemsFromSession();
            cartItems = cartItems.filter(item => item.id !== productId);
            setCartItemsToSession(cartItems);
            loadCartData();
            updateCartCount();
        }

        // Очистить корзину полностью
        document.getElementById('clear-cart').addEventListener('click', function() {
            console.log('Clearing cart...'); // Добавьте эту строку
            setCartItemsToSession([]); // Очищаем корзину в сессии
            loadCartData();
            updateCartCount();
        });

        // Переход на страницу оформления заказа
        document.getElementById('checkout').addEventListener('click', function() {
            window.location.href = '/checkout/';
        });

        // Событие для открытия и закрытия корзины
        document.getElementById('cart-button').addEventListener('click', function() {
            const cartModal = document.getElementById('cart-modal');
            const isVisible = cartModal.style.display === 'block';
            cartModal.style.display = isVisible ? 'none' : 'block'; // Переключаем видимость
            if (!isVisible) {
                loadCartData(); // Загружаем данные только при открытии
            }
        });

        // Закрыть корзину
        document.getElementById('close-cart-modal').addEventListener('click', function() {
            document.getElementById('cart-modal').style.display = 'none';
        });

        // Инициализация
        updateCartCount();
    </script>
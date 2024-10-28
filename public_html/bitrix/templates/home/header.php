<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// require_once($_SERVER['DOCUMENT_ROOT'].'/catalog/update_cart_session.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/resources/css/header.css");
Asset::getInstance()->addCss("/resources/css/footer.css");

// Получаем товары в корзине из сессии
session_start(); // Запуск сессии
$cartItemCount = "<script>document.write(localStorage.getItem('cartItems') ? JSON.parse(localStorage.getItem('cartItems')).reduce((acc, item) => acc + item.quantity, 0) : 0);</script>";
$cartItems = isset($_SESSION['cartItems']['cartItems']) ? $_SESSION['cartItems']['cartItems'] : []; // Получаем массив товаров
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
                <a href="/"><img src="/resources/img/logo/resource_1.png" alt="My Logo"></a>
            </div>
            <div class="nav-search">
                <button class="menu-toggle" onclick="toggleMenu()">&#9776;</button>
                <nav id="mainNav">
                    <a href="/">О компании</a>
                    <a href="/catalog/index.php?SECTION_ID=5">Каталог</a>
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
                        <div class="cart-modal-overlay" id="cart-modal-overlay"></div>
                        <div class="cart-modal-content">
                            <span class="close-btn" id="close-cart-modal">&times;</span>
                            <h2>Корзина</h2>
                            <div id="cart-items" class="cart-items-container"></div>
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

        // Функция для получения данных корзины из localStorage
        function getCartItems() {
            const storedData = JSON.parse(localStorage.getItem('cartItems'));
            return storedData && storedData.cartItems ? storedData.cartItems : [];
        }

        // Функция для сохранения данных корзины в localStorage
        function setCartItems(cartItems) {
            const expiryDate = Date.now() + 3 * 24 * 60 * 60 * 1000; // срок хранения - 3 дня
            const cartData = { cartItems, expiry: expiryDate };
            localStorage.setItem('cartItems', JSON.stringify(cartData));
        }

        // Функция для обновления количества товаров в корзине
        function updateCartCount() {
            const cartItems = getCartItems();
            const count = cartItems.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        // Функция для загрузки и отображения данных корзины в модальном окне
        function loadCartData() {
            const cartItems = getCartItems();
            const cartItemsContainer = document.getElementById('cart-items');
            cartItemsContainer.innerHTML = '';
            let totalSum = 0;

            if (cartItems.length === 0) {
                cartItemsContainer.innerHTML = '<p>Корзина пуста</p>';
                document.getElementById('cart-total').innerText = 'Общая сумма: 0.00 руб.';
                return;
            }

            cartItems.forEach(item => {
                const itemTotal = (item.price * item.quantity).toFixed(2);
                const imageUrl = item.image || '/path/to/default/section/image.jpg';

                const cartItemHTML = `
                    <div class="cart-item-card">
                        <img src="${imageUrl}" alt="${item.name}" class="cart-item-image">
                        <div class="cart-item-details">
                            <p class="cart-item-name">${item.name}</p>
                            <p class="cart-item-price">${item.price} руб./шт.</p>
                        </div>
                        <div class="cart-item-actions">
                            <button class="quantity-btn" onclick="updateQuantity('${item.id}', -1)">&#8722;</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" onchange="updateQuantityManual('${item.id}', this.value)">
                            <button class="quantity-btn" onclick="updateQuantity('${item.id}', 1)">&#43;</button>
                            <p class="cart-item-total">${itemTotal} руб.</p>
                            <span class="remove-item-btn" onclick="removeCartItem('${item.id}')">&#10005;</span>
                        </div>
                    </div>
                `;

                cartItemsContainer.innerHTML += cartItemHTML;
                totalSum += parseFloat(itemTotal);
            });

            document.getElementById('cart-total').innerText = `Общая сумма: ${totalSum.toFixed(2)} руб.`;
        }

        // Функция для увеличения/уменьшения количества товара
        function updateQuantity(productId, delta) {
            const cartItems = getCartItems();
            const item = cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity += delta;
                if (item.quantity < 1) {
                    removeCartItem(productId);
                } else {
                    setCartItems(cartItems);
                    loadCartData();
                }
            }
        }

        // Функция для ручного ввода количества товара
        function updateQuantityManual(productId, value) {
            const cartItems = getCartItems();
            const item = cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity = Math.max(1, parseInt(value) || 1);
                setCartItems(cartItems);
                loadCartData();
            }
        }

        // Функция для удаления товара из корзины
        function removeCartItem(productId) {
            let cartItems = getCartItems();
            cartItems = cartItems.filter(item => item.id !== productId);
            setCartItems(cartItems);
            loadCartData();
        }

        // Очистка корзины полностью
        document.getElementById('clear-cart').addEventListener('click', function() {
            localStorage.removeItem('cartItems');
            loadCartData();
            updateCartCount();
        });

        // Открытие и закрытие модального окна корзины
        document.getElementById('cart-button').addEventListener('click', function() {
            const cartModal = document.getElementById('cart-modal');
            cartModal.style.display = cartModal.style.display === 'block' ? 'none' : 'block';
            loadCartData(); // Загружаем данные корзины при открытии
        });

        document.getElementById('close-cart-modal').addEventListener('click', function() {
            document.getElementById('cart-modal').style.display = 'none';
        });

        // Функция для открытия модального окна
        function openCartModal() {
            document.getElementById('cart-modal').style.display = 'block';
            loadCartData(); // Загружаем данные корзины при открытии
        }

        // Обработчик события для кнопки открытия корзины
        document.getElementById('cart-button').addEventListener('click', openCartModal);

        // Обработчик события для кнопки закрытия модального окна
        document.getElementById('close-cart-modal').addEventListener('click', closeCartModal);

        // Закрытие модального окна при клике на затемненный фон
        document.getElementById('cart-modal-overlay').addEventListener('click', closeCartModal);

        // Инициализация и обновление количества товаров при загрузке страницы
        updateCartCount();
    </script>
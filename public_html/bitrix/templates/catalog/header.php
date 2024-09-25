<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

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
    <style>
        /* Стили для корзины */
        .cart-dropdown {
            display: none;
            position: absolute;
            background-color: white;
            border: 1px solid #ccc;
            padding: 10px;
            width: 250px;
            z-index: 1000;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
        }
    </style>
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
                </div>
                <div class="contact-info">
                    <p><a href="tel:+70000000000" class="phone-link">+7 (000) 000-00-00</a></p>
                    <button onclick="window.location.href='#Cash'">Оставить заявку</button>
                    <button onclick="toggleCartDropdown()">Корзина <span id="cart-count">0</span></button>
                    <div id="cart-dropdown" class="cart-dropdown">
                        <div id="cart-items"></div>
                        <div id="cart-total">Итого: 0 руб.</div>
                        <button onclick="clearCart()">Очистить корзину</button>
                        <button onclick="checkout()">Оформить заказ</button>
                    </div>
                </div>
            </div>
        </header>

    <script>
        let cart = {};

        function toggleCartDropdown() {
            const dropdown = document.getElementById('cart-dropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            updateCartDisplay();
        }

        function addToCart(id, article, price) {
            if (!cart[id]) {
                cart[id] = { article, quantity: 0, price };
            }
            cart[id].quantity++;
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartItemsContainer = document.getElementById('cart-items');
            cartItemsContainer.innerHTML = '';

            let total = 0;
            for (const id in cart) {
                const item = cart[id];
                total += item.price * item.quantity;

                cartItemsContainer.innerHTML += `
                    <div class="cart-item">
                        <span>${item.article} (x${item.quantity})</span>
                        <span>${item.price * item.quantity} руб.</span>
                    </div>
                `;
            }

            document.getElementById('cart-count').innerText = Object.keys(cart).length;
            document.getElementById('cart-total').innerText = `Итого: ${total} руб.`;
        }

        function clearCart() {
            cart = {};
            updateCartDisplay();
        }

        function checkout() {
            alert('Оформление заказа'); // Здесь будет ваша логика оформления заказа
        }

        window.onclick = function(event) {
            const dropdown = document.getElementById('cart-dropdown');
            if (!event.target.matches('.cart-dropdown') && !event.target.matches('button')) {
                dropdown.style.display = 'none';
            }
        };
    </script>
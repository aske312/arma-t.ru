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

                    <!-- Корзина -->
                    <div class="basket">
                        <h3>Корзина</h3>
                        <table>
                            <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Артикул</th>
                                <th>Количество</th>
                                <th>Цена</th>
                                <th>Сумма</th>
                            </tr>
                            </thead>
                            <tbody id="basket-items">

                            <!-- Товары будут добавлены динамически -->
                            </tbody>
                        </table>
                        <p>Итоговая стоимость: <span id="total-price">0</span> руб.</p>
                        <button id="checkout">Оформить заказ</button>
                        <button id="clear-cart">Очистить корзину</button>
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

            // Функция для выбора всех товаров
            function toggleSelectAll(checkbox) {
                const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
                checkboxes.forEach(item => {
                    item.checked = checkbox.checked;
                });
            }

            // Функция перехода на каталог по секциям
            function redirectToSection(sectionId) {
                window.location.href = '/catalog/catalog.php?SECTION_ID=' + sectionId;
            }

            //************ Корзина товаров **************//

            // Добавление товара в корзину
            document.querySelectorAll('.add-to-cart').forEach(function (button) {
                button.addEventListener('click', function () {
                    var productId = this.getAttribute('data-id');
                    var productArticle = this.getAttribute('data-article');
                    var productPrice = this.getAttribute('data-price');
                    addToCart(productId, productArticle, productPrice);
                });
            });

            // Добавление товара в корзину (AJAX)
            function addToCart(productId, productArticle, productPrice) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'add_to_cart.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        updateCart(JSON.parse(xhr.responseText));
                    }
                };
                xhr.send('id=' + productId + '&article=' + productArticle + '&price=' + productPrice);
            }

            // Обновление корзины
            function updateCart(basketData) {
                var basketItems = document.getElementById('basket-items');
                basketItems.innerHTML = '';

                var totalPrice = 0;

                basketData.forEach(function (item) {
                    var row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.name}</td>
                        <td>${item.article}</td>
                        <td><input type="number" value="${item.quantity}" min="1" class="quantity" data-id="${item.id}"></td>
                        <td>${item.price}</td>
                        <td>${item.total}</td>
                    `;
                    basketItems.appendChild(row);

                    totalPrice += item.total;
                });

                document.getElementById('total-price').textContent = totalPrice;

                // Изменение количества товара
                document.querySelectorAll('.quantity').forEach(function (input) {
                    input.addEventListener('change', function () {
                        var productId = this.getAttribute('data-id');
                        var quantity = this.value;
                        updateQuantity(productId, quantity);
                    });
                });
            }

            // Обновление количества товара (AJAX)
            function updateQuantity(productId, quantity) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_quantity.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        updateCart(JSON.parse(xhr.responseText));
                    }
                };
                xhr.send('id=' + productId + '&quantity=' + quantity);
            }

            // Очистка корзины
            document.getElementById('clear-cart').addEventListener('click', function () {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'clear_cart.php', true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        updateCart([]);
                    }
                };
                xhr.send();
            });

            // Оформление заказа
            document.getElementById('checkout').addEventListener('click', function () {
                window.location.href = '/checkout/';
            });
        </script>
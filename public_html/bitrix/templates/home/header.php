<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

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

// Получаем значение свойства EL_DESCRIPTION элемента инфоблока (ID = 67102, IBLOCK_ID = 3, SECTION_ID = 35)
$phone = "";
$res = CIBlockElement::GetList(
    [],
    [
        "IBLOCK_ID" => 3,  // ID инфоблока
        "SECTION_ID" => 35, // Раздел
        "ID" => 67102,      // ID элемента
        "ACTIVE" => "Y"     // Только активные элементы
    ],
    false,
    false,
    ["ID", "NAME", "PROPERTY_EL_DESCRIPTION"] // Получаем свойство EL_DESCRIPTION
);

if ($element = $res->Fetch()) {
    // Проверяем, что свойство EL_DESCRIPTION существует и содержит номер телефона
    if (!empty($element["PROPERTY_EL_DESCRIPTION_VALUE"])) {
        $phone = $element["PROPERTY_EL_DESCRIPTION_VALUE"];
    }
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
                    <a href="/catalog/index.php?SECTION_ID=22">Каталог</a>
                    <a href="/#contact">Контакты</a>
                    <a href="/#delivery">Доставка</a>
                    <a href="/#pay">Оплата</a>
                </nav>
            </div>

            <form method="GET" action="index.php">
                <input type="text" id="search" placeholder="Поиск по названию" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>

            <div class="contact-container">
                <?php if ($phone): ?>
                    <p><a href="tel:<?= preg_replace('/\D/', '', $phone) ?>" class="phone-link"><?= $phone ?></a></p>
                <?php else: ?>
                    <p><a href="tel:+70000000000" class="phone-link">+7 (000) 000-00-00</a></p>
                <?php endif; ?>

                <!-- <button onclick="window.location.href='/#Cash'">Оставить заявку</button> -->
                <div class="form-header-containers">
                    <button onclick="openForm()">Оставить заявку</button>

                    <!-- Модальное окно с формой -->
                    <div class="form-container-header" id="Form">
                        <div class="contact-form-header">
                            <span class="close-form-header" onclick="closeForm()">&times;</span>
                            <form id="contactForm-header" method="post" enctype="multipart/form-data">
                                <h2>Оставить заявку</h2>
                                <input type="text" id="name" name="name" placeholder="Ваше Имя" required>
                                <input type="email" id="email" name="email" placeholder="e-mail" required>
                                <input type="text" id="subject" name="subject" placeholder="Название компании" required>
                                <textarea id="message" name="message" rows="5" placeholder="Комментарий"></textarea>
                                <div class="form-actions-header">
                                    <button type="submit" id="contactForm-header">Отправить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cart-wrapper">
                <div class="cart-icon">
                    <button id="cart-button" class="cart-btn">
                        <div class="cart-icon-wrapper">
                            <img src="/resources/img/block/checkout.png" alt="Корзина" class="cart-icon-img">
                            <?php if ($cartItemCount > 0): ?>
                                <span id="cart-count" class="cart-count"><?= $cartItemCount ?></span>
                            <?php else: ?>
                                <span id="cart-count" class="cart-count" style="display: none;"></span>
                            <?php endif; ?>
                        </div>
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
    </header>

    <script>
        function toggleMenu() {
            document.getElementById('mainNav').classList.toggle('menu-open');
        }

        window.addEventListener('scroll', function() {
            document.getElementById('siteHeader').classList.toggle('fixed', window.scrollY > 100);
        });

        function getCartItems() {
            const storedData = JSON.parse(localStorage.getItem('cartItems'));
            return storedData && storedData.cartItems ? storedData.cartItems : [];
        }

        function setCartItems(cartItems) {
            const expiryDate = Date.now() + 3 * 24 * 60 * 60 * 1000;
            const cartData = { cartItems, expiry: expiryDate };
            localStorage.setItem('cartItems', JSON.stringify(cartData));
        }

        function updateCartCount() {
            const cartItems = getCartItems();
            const count = cartItems.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        let searchTimeout;

        document.getElementById('search').addEventListener('input', function() {
            // Очищаем таймер при каждом новом вводе
            clearTimeout(searchTimeout);

            // Устанавливаем новый таймер на 3 секунды
            searchTimeout = setTimeout(function() {
                applyFilter(); // Вызываем функцию фильтрации после 3 секунд бездействия
            }, 3000);
        });

        // Открытие формы
        function openForm() {
            document.getElementById('Form').classList.add('active');
        }

        // Закрытие формы (можно добавлять по кнопке или кликом вне формы)
        function closeForm() {
            document.getElementById('Form').classList.remove('active');
        }

        // Закрытие формы при клике вне области формы
        window.onclick = function(event) {
            if (event.target === document.getElementById('Form')) {
                closeForm();
            }
        }

        function loadCartData() {
            const cartItems = getCartItems();
            const cartItemsContainer = document.getElementById('cart-items');
            cartItemsContainer.innerHTML = '';
            let totalSum = 0;

            if (cartItems.length === 0) {
                cartItemsContainer.innerHTML = '<p>Корзина пуста</p>';
                document.getElementById('cart-total').innerText = 'Общая сумма: Под заказ';
                return;
            }

            cartItems.forEach(async (item) => {
                const itemTotal = (item.price * item.quantity).toFixed(2);
                totalSum += parseFloat(itemTotal); // Считаем общую сумму здесь

                // Если у товара нет изображения, запрашиваем изображение раздела
                let imageUrl = item.image || '/resources/img/production/0.png';
                if (!item.image) {
                    imageUrl = await fetchSectionImage(item.id);
                }

                const cartItemHTML = `
                    <div class="cart-item-card">
                        <img src="${imageUrl}" alt="${item.name}" class="cart-item-image">
                        <div class="cart-item-details">
                            <p class="cart-item-name">${item.name}</p>
                            <p class="cart-item-article">Артикул: ${item.article}</p>
                            <p class="cart-item-price">
                                ${item.price && item.price > 0 ? item.price + ' руб./шт.' : 'Цена под заказ'}
                            </p>
                        </div>
                        <div class="cart-item-actions">
                            <button class="quantity-btn" onclick="updateQuantity('${item.id}', -1)">&#8722;</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" onchange="updateQuantityManual('${item.id}', this.value)">
                            <button class="quantity-btn" onclick="updateQuantity('${item.id}', 1)">&#43;</button>
                            <span class="cart-item-total">${itemTotal} руб.</span>
                            <span class="remove-item-btn" onclick="removeCartItem('${item.id}')">&#10005;</span>
                        </div>
                    </div>
                `;

                cartItemsContainer.innerHTML += cartItemHTML;
            });

            // Проверяем общую сумму и выводим "Под заказ", если сумма равна 0
            document.getElementById('cart-total').innerText = totalSum > 0
                ? `Общая сумма: ${totalSum.toFixed(2)} руб.`
                : 'Общая сумма: Под заказ';
        }

        // Функция для получения изображения раздела
        async function fetchSectionImage(itemId) {
            try {
                const response = await fetch(`/getSectionImage.php?itemId=${itemId}`);
                const data = await response.json();
                return data.imageUrl;
            } catch (error) {
                console.error('Ошибка при получении изображения раздела:', error);
                return '/resources/img/production/0.png';
            }
        }

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

        function updateQuantityManual(productId, value) {
            const cartItems = getCartItems();
            const item = cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity = Math.max(1, parseInt(value) || 1);
                setCartItems(cartItems);
                loadCartData();
            }
        }

        function removeCartItem(productId) {
            let cartItems = getCartItems();
            cartItems = cartItems.filter(item => item.id !== productId);
            setCartItems(cartItems);
            loadCartData();
        }

        document.getElementById('clear-cart').addEventListener('click', function() {
            localStorage.removeItem('cartItems');
            loadCartData();
            updateCartCount();
        });

        updateCartCount();

        document.getElementById('checkout').addEventListener('click', function() {
            window.location.href = '/checkout/';
        });

        document.getElementById('cart-button').addEventListener('click', function() {
            const cartModal = document.getElementById('cart-modal');
            cartModal.style.display = cartModal.style.display === 'block' ? 'none' : 'block';
            loadCartData();
        });

        document.getElementById('close-cart-modal').addEventListener('click', function() {
            document.getElementById('cart-modal').style.display = 'none';
        });

        document.getElementById('cart-modal-overlay').addEventListener('click', function() {
            document.getElementById('cart-modal').style.display = 'none';
        });

        // Закрытие модального окна при клике вне формы
        window.onclick = function(event) {
            if (event.target === document.getElementById('Form')) {
                document.getElementById('Form').classList.remove('active');
            }
        };

        // Закрытие формы по кнопке
        document.querySelector('.close-form-header').addEventListener('click', function() {
            document.getElementById('Form').classList.remove('active');
        });

        // Обработчик отправки формы
        document.getElementById('contactForm-header').addEventListener('submit', function (e) {
            e.preventDefault();  // Предотвращаем перезагрузку страницы

//            // Получаем ответ капчи
//            var recaptchaResponse = grecaptcha.getResponse();
//
//            // Проверяем, прошел ли пользователь капчу
//            if (recaptchaResponse.length === 0) {
//                alert("Пожалуйста, подтвердите, что вы не робот.");
//                return; // Если капча не пройдена, не отправляем форму
//            }
//
//            // Собираем данные формы
            var formData = new FormData(this);
//            formData.append('g-recaptcha-response', recaptchaResponse); // Добавляем ответ капчи в форму

            // Создаем и отправляем запрос
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/resources/src/send.php', true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('contactForm').reset();
                    document.getElementById('fileList').innerHTML = '';  // Очищаем список прикрепленных файлов
                    alert('Ваше сообщение было успешно отправлено!');
                } else {
                    alert('Произошла ошибка при отправке сообщения.');
                }
            };

            xhr.send(formData);  // Отправляем данные формы
        });
    </script>
<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// MOBILE VERSION
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|Windows Phone|Opera Mini|IEMobile/i', $userAgent);

if ($isMobile) {
    include 'm/header.php';
    return;
}

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
        "IBLOCK_ID" => 4,  // ID инфоблока
        //"SECTION_ID" => 3, // Раздел
        "ID" => 10,      // ID элемента
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

$email = "";
$res = CIBlockElement::GetList(
    [],
    [
        "IBLOCK_ID" => 4,  // ID инфоблока
        //"SECTION_ID" => 3, // Раздел
        "ID" => 9,      // ID элемента
        "ACTIVE" => "Y"     // Только активные элементы
    ],
    false,
    false,
    ["ID", "NAME", "PROPERTY_EL_DESCRIPTION"] // Получаем свойство EL_DESCRIPTION
);

if ($element = $res->Fetch()) {
    // Проверяем, что свойство EL_DESCRIPTION существует и содержит номер телефона
    if (!empty($element["PROPERTY_EL_DESCRIPTION_VALUE"])) {
        $email = $element["PROPERTY_EL_DESCRIPTION_VALUE"];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <?php $APPLICATION->ShowHead(); ?>
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
</head>
<body>
    <div id="panel"><?php $APPLICATION->ShowPanel(); ?></div>
    <header id="siteHeader" class="header">
        <div class="header-content">
            <div class="logo">
                <a href="/"><img src="/resources/img/logo/resource_1.png" alt="ARMA-T.RU"></a>
            </div>

            <div class="nav-search">
                <!-- <button class="menu-toggle" onclick="toggleMenu()">&#9776;</button> -->
                <nav id="mainNav">
                    <a href="/">О компании</a>
                    <a href="/catalog/index.php?SECTION_ID=1">Каталог</a>
                    <a href="/#contact">Контакты</a>
                    <a href="/#delivery-title">Доставка</a>
                    <a href="/#pay">Оплата</a>
                </nav>

                <!-- Расширенная поисковая строка -->
                <form class="nav-search-form" method="GET" action="index.php">
                    <input type="text" id="search" class="full-width-search" placeholder="Поиск...">
                    <div id="suggestions"></div>
                </form>
            </div>

            <div class="contact-container">
                <?php if ($email): ?>
                    <p><a href="mailto:<?= htmlspecialchars($email) ?>" class="phone-link"><?= htmlspecialchars($email) ?></a></p>
                <?php else: ?>
                    <p><a href="mailto:info@arma-t.ru" class="phone-link">info@arma-t.ru</a></p>
                <?php endif; ?>

                <?php if ($phone): ?>
                    <p><a href="tel:<?= preg_replace('/\D/', '', $phone) ?>" class="phone-link"><?= $phone ?></a></p>
                <?php else: ?>
                    <p><a href="tel:+70000000000" class="phone-link">+7(000) 000-00-00</a></p>
                <?php endif; ?>

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
                                    <label for="newsletter" class="newsletter-label-header">
                                        <input type="checkbox" id="newsletter" name="newsletter" required>
                                        Согласие на рассылку
                                    </label>
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
                            <?php if ($cartItems > 0): ?>
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
                        <div class="cart-modal-footer">
                            <button id="clear-cart" class="button">Очистить корзину</button>
                            <button id="checkout" class="button">Оформить заказ</button>
                        </div>
                    </div>
                </div>
            </div>

        <div id="cookie-banner" class="cookie-banner">
            <p>Мы используем файлы cookie для улучшения работы сайта. Оставаясь на сайте, вы соглашаетесь с <a href="/policy">политикой использования cookie</a>.</p>
            <button id="accept-cookies">Принять</button>
        </div>
        </div>
    </header>

<script>
    window.addEventListener('scroll', function() {
        document.getElementById('siteHeader').classList.toggle('fixed', window.scrollY > 100);
    });

    function getCartItems() {
        const storedData = JSON.parse(localStorage.getItem('cartItems'));
        return storedData && storedData.cartItems ? storedData.cartItems : [];
    }

    document.addEventListener("DOMContentLoaded", function() {
        let errorBox = document.querySelector('.bitrix-error-box');
        if (errorBox) {
            errorBox.style.display = 'none';
        }
    });

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

    // *** Поиск ***//
    document.getElementById('search').addEventListener('input', function() {
        const query = this.value;

        // Если строка пустая, убираем предложения
        if (!query) {
            document.getElementById('suggestions').innerHTML = '';
            document.getElementById('suggestions').style.display = 'none';
            return;
        }

        // Отправляем запрос на сервер для получения предложений
        fetch(`/resources/src/search_suggestions.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                const suggestionsDiv = document.getElementById('suggestions');
                suggestionsDiv.innerHTML = ''; // Очищаем текущие предложения

                // Если есть предложения
                if (data.length > 0) {
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.textContent = item.name;
                        div.classList.add('suggestion-item');  // Добавляем класс для стилей
                        div.onclick = () => window.location.href = item.url;  // Перенаправление при клике
                        suggestionsDiv.appendChild(div);
                    });
                    // Показываем блок с предложениями
                    suggestionsDiv.style.display = 'block';
                } else {
                    suggestionsDiv.innerHTML = 'Ничего не найдено';
                    suggestionsDiv.style.display = 'block';
                }
            })
            .catch(error => console.error('Error fetching search suggestions:', error));
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
                    <a href="/detail/index.php?ID=${item.id}" class="cart-item-link">
                        <img src="${imageUrl}" alt="${item.name}" class="cart-item-image">
                        <div class="cart-item-details">
                            <p class="cart-item-name">${item.name}</p>
                            <p class="cart-item-article">Артикул: ${item.article}</p>
                            <p class="cart-item-price">
                                ${item.price && item.price > 0 ? item.price + ' руб./шт.' : 'Цена под заказ'}
                            </p>
                        </div>
                    </a>
                        <div class="cart-item-actions">
                            <button class="quantity-btn" onclick="updateQuantity('${item.id}', -1)">&#8722;</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" onchange="updateQuantityManual('${item.id}', this.value)">
                            <button class="quantity-btn" onclick="updateQuantity('${item.id}', 1)">&#43;</button>
                            <span class="cart-item-total">
                                ${!isNaN(itemTotal) && itemTotal !== null && itemTotal > 0 ? itemTotal + ' руб.' : ''}
                            </span>
                            <span class="remove-item-btn" onclick="removeCartItem('${item.id}')">&#10005;</span>
                        </div>
                </div>
            `;

            cartItemsContainer.innerHTML += cartItemHTML;
        });

        // Проверяем общую сумму и выводим "Под заказ", если сумма равна 0
        document.getElementById('cart-total').innerText = totalSum > 0
            ? `Общая сумма: ${totalSum.toFixed(2)} руб.`
            : 'Общая сумма: под заказ';
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
        window.location.href = '/checkout';
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

    document.getElementById('contactForm-header').addEventListener('submit', function (e) {
        e.preventDefault();  // Предотвращаем перезагрузку страницы

        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true; // Блокируем кнопку

        const formData = new FormData(this);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/resources/src/send.php', true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('contactForm-header').reset();
                alert('Ваше сообщение было успешно отправлено!');
                setTimeout(() => {
                    submitButton.disabled = false;
                }, 1800); // 180000 мс = 3 минуты
            } else {
                alert('Произошла ошибка при отправке сообщения.');
                submitButton.disabled = false;
            }
        };

        xhr.onerror = function () {
            alert('Произошла ошибка при отправке сообщения.');
            submitButton.disabled = false;
        };

        xhr.send(formData);
    });

    // Функция для изменения размеров окна корзины
    function setupCartModalResize() {
        const cartModalContent = document.querySelector('.cart-modal-content');
        const resizeHandle = document.createElement('div');
        resizeHandle.className = 'resize-handle';
        cartModalContent.appendChild(resizeHandle);

        let isResizing = false;

        resizeHandle.addEventListener('mousedown', (e) => {
            e.preventDefault();
            isResizing = true;
            document.addEventListener('mousemove', resizeModal);
            document.addEventListener('mouseup', stopResize);
        });

        function resizeModal(e) {
            if (isResizing) {
                const newWidth = e.clientX - cartModalContent.getBoundingClientRect().left;
                const newHeight = e.clientY - cartModalContent.getBoundingClientRect().top;

                if (newWidth > 400 && newWidth < 800) {
                    cartModalContent.style.width = `${newWidth}px`;
                }
                if (newHeight > 400 && newHeight < 600) {
                    cartModalContent.style.height = `${newHeight}px`;
                }
            }
        }

        function stopResize() {
            isResizing = false;
            document.removeEventListener('mousemove', resizeModal);
            document.removeEventListener('mouseup', stopResize);
        }
    }

    // Функция для перетаскивания окна корзины
    function setupCartModalDrag() {
        const cartModalContent = document.querySelector('.cart-modal-content');
        const cartHeader = cartModalContent.querySelector('h2');

        let isDragging = false;
        let offsetX, offsetY;

        cartHeader.addEventListener('mousedown', (e) => {
            isDragging = true;
            offsetX = e.clientX - cartModalContent.getBoundingClientRect().left;
            offsetY = e.clientY - cartModalContent.getBoundingClientRect().top;
            document.addEventListener('mousemove', dragModal);
            document.addEventListener('mouseup', stopDrag);
        });

        function dragModal(e) {
            if (isDragging) {
                cartModalContent.style.left = `${e.clientX - offsetX}px`;
                cartModalContent.style.top = `${e.clientY - offsetY}px`;
            }
        }

        function stopDrag() {
            isDragging = false;
            document.removeEventListener('mousemove', dragModal);
            document.removeEventListener('mouseup', stopDrag);
        }
    }

    // Инициализация функций при открытии корзины
    document.getElementById('cart-button').addEventListener('click', function() {
        const cartModal = document.getElementById('cart-modal');
        cartModal.style.display = cartModal.style.display === 'block' ? 'none' : 'block';
        loadCartData();
        setupCartModalResize();
    });

    document.addEventListener("DOMContentLoaded", function() {
        const banner = document.getElementById("cookie-banner");
        const acceptBtn = document.getElementById("accept-cookies");

        if (!localStorage.getItem("cookiesAccepted")) {
            banner.style.display = "block";
        }

        acceptBtn.addEventListener("click", function() {
            localStorage.setItem("cookiesAccepted", "true");
            banner.style.display = "none";
        });
    });

    document.cookie = "cookiesAccepted=true; path=/; max-age=31536000";
</script>
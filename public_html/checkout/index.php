<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // Подключение CSS
?>

<div class="section-title">
    <h2>Оформление заказа</h2>
    <p>Товары в корзине</p>
</div>

<div id="cart-items" class="product-checkout">
    <!-- Здесь будет вывод товаров из localStorage -->
</div>

<p id="cart-total"></p> <!-- Общая сумма товаров -->

<!-- Кнопка для обновления содержимого корзины -->
<button class="refresh-button" onclick="refreshCart()">Обновить корзину</button>

<button class="back-button" onclick="history.back()">Назад</button> <!-- Кнопка Назад -->

<form class="order-form" id="order-form">
    <h2>Ваши данные</h2>
    <label for="name">Имя:</label>
    <input type="text" id="name" name="name" required>
    <label for="company">Компания:</label>
    <input type="text" id="company" name="company" required>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <label for="address">Адрес:</label>
    <textarea id="address" name="address" required></textarea>
    <button type="submit">Оформить заказ</button>
</form>

<script>
    function getCartItems() {
        const storedData = JSON.parse(localStorage.getItem('cartItems'));
        return storedData && storedData.cartItems ? storedData.cartItems : [];
    }

    function setCartItems(cartItems) {
        const expiryDate = Date.now() + 3 * 24 * 60 * 60 * 1000;
        const cartData = { cartItems, expiry: expiryDate };
        localStorage.setItem('cartItems', JSON.stringify(cartData));
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
            totalSum += parseFloat(itemTotal);

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

        document.getElementById('cart-total').innerText = totalSum > 0
            ? `Общая сумма: ${totalSum.toFixed(2)} руб.`
            : 'Общая сумма: Под заказ';
    }

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

    // Функция для обновления содержимого корзины
    function refreshCart() {
        loadCartData();
    }

    document.addEventListener("DOMContentLoaded", function() {
        loadCartData();
    });

    const orderForm = document.getElementById('order-form');
    orderForm.addEventListener('submit', (event) => {
        event.preventDefault();
        alert("Заказ оформлен!"); // Здесь можно добавить обработку отправки формы
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

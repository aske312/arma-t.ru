<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // Подключение CSS
session_start();
?>

<div class="section-title">
    <h2>Оформление заказа</h2>
    <p>Товары в корзине</p>
</div>

<div id="cart-items" class="product-checkout">
    <!-- Здесь будет динамически выводиться содержимое корзины из localStorage -->
</div>

<div class="cart-summary">
    <p id="cart-total">Общая сумма: 0 руб.</p>
</div>

<button class="back-button" onclick="history.back()">Назад</button>

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
    document.addEventListener("DOMContentLoaded", function() {
        // Функция для загрузки и отображения корзины
        function loadCartData() {
            const cartData = JSON.parse(localStorage.getItem('cartItems'));
            const cartItemsContainer = document.getElementById('cart-items');
            let totalSum = 0;

            cartItemsContainer.innerHTML = ''; // Очистка контейнера

            if (!cartData || !cartData.cartItems || cartData.cartItems.length === 0) {
                cartItemsContainer.innerHTML = "<p>Ваша корзина пуста.</p>";
                document.getElementById('cart-total').innerText = 'Общая сумма: 0 руб.';
                return;
            }

            cartData.cartItems.forEach(item => {
                const itemPrice = parseFloat(item.price) || 0;
                const itemTotal = itemPrice * item.quantity;
                totalSum += itemTotal;

                const cartItemHTML = `
                    <div class="cart-item">
                        <h3>${item.name}</h3>
                        <p>Артикул: ${item.article || 'Нет данных'}</p>
                        <p>Цена: ${item.price > 0 ? item.price + ' руб.' : 'Цена под заказ'}</p>
                        <div class="quantity-control">
                            <button onclick="updateQuantity('${item.id}', -1)">-</button>
                            <input type="number" value="${item.quantity}" onchange="updateQuantityManual('${item.id}', this.value)">
                            <button onclick="updateQuantity('${item.id}', 1)">+</button>
                        </div>
                        <p>Сумма: ${itemTotal > 0 ? itemTotal + ' руб.' : 'Под заказ'}</p>
                        <button onclick="removeCartItem('${item.id}')">Удалить</button>
                    </div>
                `;
                cartItemsContainer.innerHTML += cartItemHTML;
            });

            document.getElementById('cart-total').innerText = totalSum > 0
                ? `Общая сумма: ${totalSum.toFixed(2)} руб.`
                : 'Общая сумма: Под заказ';
        }

        // Функция для изменения количества товара
        function updateQuantity(productId, delta) {
            const cartData = JSON.parse(localStorage.getItem('cartItems'));
            const item = cartData.cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity += delta;
                if (item.quantity < 1) {
                    removeCartItem(productId);
                } else {
                    setCartItems(cartData.cartItems);
                    loadCartData();
                }
            }
        }

        // Функция для ручного изменения количества товара
        function updateQuantityManual(productId, value) {
            const cartData = JSON.parse(localStorage.getItem('cartItems'));
            const item = cartData.cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity = Math.max(1, parseInt(value) || 1);
                setCartItems(cartData.cartItems);
                loadCartData();
            }
        }

        // Функция для удаления товара из корзины
        function removeCartItem(productId) {
            let cartData = JSON.parse(localStorage.getItem('cartItems'));
            cartData.cartItems = cartData.cartItems.filter(item => item.id !== productId);
            setCartItems(cartData.cartItems);
            loadCartData();
        }

        // Функция для сохранения корзины в localStorage
        function setCartItems(cartItems) {
            const expiryDate = Date.now() + 3 * 24 * 60 * 60 * 1000;
            localStorage.setItem('cartItems', JSON.stringify({ cartItems, expiry: expiryDate }));
        }

        loadCartData(); // Загружаем корзину при загрузке страницы
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

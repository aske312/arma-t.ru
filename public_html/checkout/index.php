<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // Подключение CSS
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
        loadCartData();

        // Загрузка данных из localStorage и отображение товаров
        function loadCartData() {
            const data = JSON.parse(localStorage.getItem('cartItems'));
            const cartItemsContainer = document.getElementById('cart-items');
            let totalSum = 0;

            // Очищаем контейнер перед заполнением
            cartItemsContainer.innerHTML = '';

            if (!data || !data.cartItems || data.cartItems.length === 0) {
                cartItemsContainer.innerHTML = "<p>Ваша корзина пуста.</p>";
                document.getElementById('cart-total').innerText = 'Общая сумма: 0 руб.';
                return;
            }

            data.cartItems.forEach(item => {
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

            // Обновление общей суммы
            document.getElementById('cart-total').innerText = totalSum > 0
                ? `Общая сумма: ${totalSum.toFixed(2)} руб.`
                : 'Общая сумма: Под заказ';
        }

        // Функция для обновления количества товара
        function updateQuantity(productId, delta) {
            const data = JSON.parse(localStorage.getItem('cartItems'));
            const item = data.cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity += delta;
                if (item.quantity < 1) {
                    removeCartItem(productId);
                } else {
                    setCartItems(data.cartItems);
                    loadCartData();
                }
            }
        }

        // Функция для ручного изменения количества товара
        function updateQuantityManual(productId, value) {
            const data = JSON.parse(localStorage.getItem('cartItems'));
            const item = data.cartItems.find(item => item.id === productId);

            if (item) {
                item.quantity = Math.max(1, parseInt(value) || 1);
                setCartItems(data.cartItems);
                loadCartData();
            }
        }

        // Функция для удаления товара из корзины
        function removeCartItem(productId) {
            let data = JSON.parse(localStorage.getItem('cartItems'));
            data.cartItems = data.cartItems.filter(item => item.id !== productId);
            setCartItems(data.cartItems);
            loadCartData();
        }

        // Сохранение корзины в localStorage
        function setCartItems(cartItems) {
            const expiryDate = Date.now() + 3 * 24 * 60 * 60 * 1000; // Устанавливаем срок действия на 3 дня
            localStorage.setItem('cartItems', JSON.stringify({ cartItems, expiry: expiryDate }));
        }

        // Обработка отправки формы
        document.getElementById('order-form').addEventListener('submit', function(event) {
            event.preventDefault();
            alert("Заказ оформлен!");
        });
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

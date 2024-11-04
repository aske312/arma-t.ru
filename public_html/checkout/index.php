<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // Подключение CSS
?>

<div class="section-title">
    <h2>Оформление заказа</h2> <!-- Название раздела -->
    <p>Товары в корзине</p>
</div>

<div id="cart-items" class="product-checkout">
    <!-- Здесь будет вывод товаров из localStorage -->
</div>

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
    document.addEventListener("DOMContentLoaded", function() {
        const data = JSON.parse(localStorage.getItem('cartItems'));
        const cartItemsContainer = document.getElementById('cart-items');

        // Проверка наличия товаров в корзине
        if (data && data.cartItems && data.cartItems.length > 0) {
            data.cartItems.forEach(item => {
                cartItemsContainer.innerHTML += `
                    <div class="cart-item">
                        <h3>${item.name}</h3>
                        <p>Цена: ${item.price} руб.</p>
                        <p>Количество: ${item.quantity}</p>
                    </div>
                `;
            });
        } else {
            cartItemsContainer.innerHTML = "<p>Ваша корзина пуста.</p>";
        }
    });

    const orderForm = document.getElementById('order-form');
    orderForm.addEventListener('submit', (event) => {
        event.preventDefault();
        alert("Заказ оформлен!"); // Здесь можно добавить обработку отправки формы
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

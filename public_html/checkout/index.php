<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

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

<form class="order-form" id="order-form" method="POST" action="/order/process_order.php"> <!-- Обработка формы -->
    <h2>Ваши данные</h2>
    <label for="name">Имя:</label>
    <input type="text" id="name" name="name" required>
    <label for="company">Компания:</label>
    <input type="text" id="company" name="company">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <label for="address">Адрес:</label>
    <textarea id="address" name="address" required></textarea>
    <button type="submit">Оформить заказ</button>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const cartData = JSON.parse(localStorage.getItem('cartItems'));
        const cartItemsContainer = document.getElementById('cart-items');

        // Проверка наличия товаров в корзине
        if (cartData && cartData.cartItems && cartData.cartItems.length > 0) {
            let totalAmount = 0;
            cartData.cartItems.forEach(item => {
                const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
                totalAmount += itemTotal;

                cartItemsContainer.innerHTML += `
                    <div class="cart-item">
                        <h3>${item.name}</h3>
                        <p>Цена: ${item.price} руб.</p>
                        <p>Количество: ${item.quantity}</p>
                        <p>Всего: ${itemTotal.toFixed(2)} руб.</p>
                    </div>
                `;
            });
            cartItemsContainer.innerHTML += `<p class="total-amount">Итоговая сумма: ${totalAmount.toFixed(2)} руб.</p>`;
        } else {
            cartItemsContainer.innerHTML = "<p>Ваша корзина пуста.</p>";
        }
    });

    // Обработчик формы заказа
    document.getElementById('order-form').addEventListener('submit', (event) => {
        event.preventDefault();

        const orderData = {
            name: document.getElementById('name').value,
            company: document.getElementById('company').value,
            email: document.getElementById('email').value,
            address: document.getElementById('address').value,
            cartItems: JSON.parse(localStorage.getItem('cartItems')).cartItems
        };

        // Отправка данных на сервер
        fetch('/order/process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Заказ успешно оформлен!");
                localStorage.removeItem('cartItems'); // Очищаем корзину после успешного заказа
                window.location.href = '/order/confirmation.php'; // Перенаправление на страницу подтверждения
            } else {
                alert("Ошибка при оформлении заказа. Пожалуйста, попробуйте снова.");
            }
        })
        .catch(error => {
            console.error('Ошибка при отправке заказа:', error);
            alert("Ошибка при отправке заказа. Пожалуйста, попробуйте позже.");
        });
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

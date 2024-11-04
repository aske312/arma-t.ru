<?php
session_start(); // Обязательно нужно стартовать сессию
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // CSS
?>

<div class="section-title">
    <h2>Оформление заказа</h2> <!-- Название раздела -->
    <p>Товары в корзине</p>
</div>

<div id="cart-items">
    <div class="product-checkout">
        <?php
        // Получение данных из сессии
        $cartItems = isset($_SESSION['cartItems']) ? $_SESSION['cartItems'] : [];

        // Отображение товаров
        if (!empty($cartItems)) {
            foreach ($cartItems as $item) {
                echo "<div class='cart-item'>";
                echo "<h3>{$item['name']}</h3>";
                echo "<p>Цена: {$item['price']} руб.</p>";
                echo "<p>Количество: {$item['quantity']}</p>";
                echo "</div>";
            }
        } else {
            echo "<p>Ваша корзина пуста.</p>";
        }
        ?>

        <button class="back-button" onclick="history.back()">Назад</button> <!-- Кнопка Назад -->
    </div>

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
</div>

<script>
    const orderForm = document.getElementById('order-form');
    orderForm.addEventListener('submit', (event) => {
        event.preventDefault();
        // Здесь вы можете добавить обработку отправки формы
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

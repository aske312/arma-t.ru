<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/local/css/checkout.css"); //css

?>
<div class="section-title">
    <h2><?php echo htmlspecialchars($productName); ?></h2> <!-- Название раздела -->
    <p>Подробное описание</p>
</div>

<h1>Оформление заказа</h1>
<div id="cart-items">
    <h2>Товары в корзине</h2>
    <?php
    // Получение данных из куки
    $cartItems = json_decode($_COOKIE['cartItems']);

    // Отображение товаров
    if (!empty($cartItems)) {
        foreach ($cartItems as $item) {
            echo "<div class='cart-item'>";
            echo "<h3>{$item->name}</h3>";
            echo "<p>Цена: {$item->price}</p>";
            echo "<p>Количество: {$item->quantity}</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Ваша корзина пуста.</p>";
    }
    ?>
</div>

<form class="order-form" id="order-form">
    <h2>Ваши данные</h2>
    <label for="name">Имя:</label>
    <input type="text" id="name" name="name" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <label for="address">Адрес:</label>
    <textarea id="address" name="address" required></textarea>

    <button type="submit">Оформить заказ</button>
</form>

<script>
    // Обработка отправки формы
    const orderForm = document.getElementById('order-form');
    orderForm.addEventListener('submit', (event) => {
        event.preventDefault();
        // Здесь вы должны добавить код для отправки данных формы на сервер
        // Например, используя AJAX или FormData
        // После успешной отправки вы можете очистить корзину, перенаправить пользователя на страницу подтверждения заказа и т.д.
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
session_start();

// Если корзина пуста
if (empty($_SESSION['CART'])) {
    echo "<h2>Ваша корзина пуста</h2>";
} else {
    echo "<h2>Ваша корзина</h2>";
    echo '<table class="cart-table">';
    echo '<tr><th>Артикул</th><th>Цена</th><th>Количество</th><th>Итого</th><th>Удалить</th></tr>';

    $totalPrice = 0;

    foreach ($_SESSION['CART'] as $productId => $product) {
        $itemTotalPrice = $product['PRICE'] * $product['QUANTITY'];
        $totalPrice += $itemTotalPrice;

        echo '<tr>';
        echo '<td>' . htmlspecialchars($product['ARTICUL']) . '</td>';
        echo '<td>' . number_format($product['PRICE'], 2, '.', '') . ' руб.</td>';
        echo '<td>' . $product['QUANTITY'] . '</td>';
        echo '<td>' . number_format($itemTotalPrice, 2, '.', '') . ' руб.</td>';
        echo '<td><button onclick="removeFromCart(' . $productId . ')">Удалить</button></td>';
        echo '</tr>';
    }

    echo '<tr><td colspan="3">Общая стоимость:</td><td>' . number_format($totalPrice, 2, '.', '') . ' руб.</td></tr>';
    echo '</table>';
}
?>

<!-- Скрипт для удаления товаров из корзины -->
<script>
function removeFromCart(productId) {
    $.ajax({
        type: 'POST',
        url: '/local/ajax/remove_from_cart.php',
        data: { id: productId },
        success: function(response) {
            alert('Товар удален из корзины');
            location.reload(); // Перезагрузка страницы
        }
    });
}
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

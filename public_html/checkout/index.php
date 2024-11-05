<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // Подключение CSS
?>

<h2>Корзина покупок</h2>
<div id="cart-items"></div>
<div class="cart-summary" id="cart-total"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadCartData();

        // Функция для загрузки данных из localStorage и отображения их
        function loadCartData() {
            const cartData = JSON.parse(localStorage.getItem('cartItemsData'));
            const cartItemsContainer = document.getElementById('cart-items');
            const totalElement = document.getElementById('cart-total');

            // Очищаем контейнер перед заполнением
            cartItemsContainer.innerHTML = '';
            totalElement.innerHTML = '';

            if (!cartData || !Array.isArray(cartData.cartItems) || cartData.cartItems.length === 0) {
                cartItemsContainer.innerHTML = "<p>Ваша корзина пуста.</p>";
                totalElement.innerHTML = "Общая сумма: 0 руб.";
                return;
            }

            let totalSum = 0;

            // Перебираем все товары в корзине
            cartData.cartItems.forEach(item => {
                const itemPrice = parseFloat(item.price) || 0;
                const itemTotal = itemPrice * item.quantity;
                totalSum += itemTotal;

                const cartItemHTML = `
                    <div class="cart-item">
                        <h3>${item.name}</h3>
                        <p><strong>Артикул:</strong> ${item.article || 'Не указан'}</p>
                        <p><strong>Цена:</strong> ${itemPrice.toFixed(2)} руб.</p>
                        <p><strong>Количество:</strong> ${item.quantity}</p>
                        <p><strong>Сумма:</strong> ${itemTotal.toFixed(2)} руб.</p>
                    </div>
                `;

                cartItemsContainer.innerHTML += cartItemHTML;
            });

            // Отображаем общую сумму
            totalElement.innerHTML = `Общая сумма: ${totalSum.toFixed(2)} руб.`;
        }
    });
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

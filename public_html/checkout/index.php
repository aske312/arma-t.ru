<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // Подключение CSS
?>

    <h1>Корзина покупок</h1>
    <div id="cart-items-container"></div>
    <div class="total" id="total-amount">Общая сумма: 0 ₽</div>

<script>
    // Функция для получения данных из localStorage и обработки их
    function loadCart() {
        // Получаем строку данных из localStorage по ключу "cartItem"
        let cartDataString = localStorage.getItem('cartItem');

        // Если данные есть, парсим их
        if (cartDataString) {
            try {
                let cartData = JSON.parse(cartDataString);

                // Проверяем, что cartItems - это массив
                if (cartData && Array.isArray(cartData.cartItems)) {
                    // Контейнер для товаров
                    let cartItemsContainer = document.getElementById('cart-items-container');
                    cartItemsContainer.innerHTML = ''; // Очищаем контейнер перед добавлением новых данных

                    // Перебираем товары и выводим их
                    cartData.cartItems.forEach(item => {
                        let itemDiv = document.createElement('div');
                        itemDiv.classList.add('cart-item');

                        itemDiv.innerHTML = `
                            <span><strong>Название:</strong> ${item.name}</span>
                            <span><strong>Цена:</strong> ${item.price} ₽</span>
                            <span><strong>Количество:</strong> ${item.quantity}</span>
                        `;

                        cartItemsContainer.appendChild(itemDiv);
                    });

                    // Рассчитываем общую сумму
                    let totalAmount = cartData.cartItems.reduce((total, item) => {
                        let price = parseFloat(item.price) || 0; // Преобразуем цену в число
                        return total + (price * item.quantity); // Умножаем цену на количество
                    }, 0);

                    // Обновляем значение общей суммы на странице
                    document.getElementById('total-amount').innerText = `Общая сумма: ${totalAmount} ₽`;

                } else {
                    console.error("Неверная структура данных в cartItems");
                }
            } catch (error) {
                console.error("Ошибка при парсинге данных из localStorage:", error);
            }
        } else {
            console.log("Нет данных в localStorage по ключу 'cartItem'");
        }
    }

    // Загружаем корзину при загрузке страницы
    window.onload = loadCart;
</script>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

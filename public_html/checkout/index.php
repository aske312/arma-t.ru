<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // Подключение CSS
?>

<div class="section-title">
    <h2>Оформление заказа</h2> <!-- Название раздела -->
    <p>Товары в корзине</p>
</div>

<div id="cart-items">
    <div class="product-checkout" id="product-checkout">
        <!-- Здесь будет вывод корзины -->
    </div>
    <div class="total" id="total-amount">Общая сумма: 0 ₽</div> <!-- Общая сумма внутри блока корзины -->
</div>

<button onclick="history.back()" class="back-button">Назад</button>

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
    // Функция для получения данных из localStorage и обработки их
    function loadCart() {
        // Получаем строку данных из localStorage по ключу "cartItems"
        let cartDataString = localStorage.getItem('cartItems');

        // Если данные есть, парсим их
        if (cartDataString) {
            try {
                let cartData = JSON.parse(cartDataString);

                // Проверяем, что cartItems - это массив
                if (cartData && Array.isArray(cartData.cartItems)) {
                    // Контейнер для товаров
                    let cartItemsContainer = document.getElementById('product-checkout');
                    cartItemsContainer.innerHTML = ''; // Очищаем контейнер перед добавлением новых данных

                    // Перебираем товары и выводим их
                    cartData.cartItems.forEach((item, index) => {
                        let itemDiv = document.createElement('div');
                        itemDiv.classList.add('cart-item');

                        // Если цена = 0, показываем "Под заказ"
                        let priceText = item.price == 0 ? "Под заказ" : `${item.price} ₽`;

                        itemDiv.innerHTML = `
                            <div class="product-details">
                                <div class="product-image">
                                    <img src="${item.image}" alt="${item.name}">
                                </div>
                                <div class="product-info">
                                    <span><strong>Название:</strong> ${item.name}</span>
                                    <span><strong>Артикул:</strong> ${item.sku}</span>
                                    <span><strong>Цена:</strong> ${priceText}</span>
                                    <div class="quantity-control">
                                        <button class="quantity-btn minus" data-index="${index}">-</button>
                                        <input type="number" value="${item.quantity}" min="0" class="quantity-input" data-index="${index}" />
                                        <button class="quantity-btn plus" data-index="${index}">+</button>
                                    </div>
                                    <button class="remove-button" data-index="${index}">❌ Удалить</button>
                                </div>
                            </div>
                        `;

                        cartItemsContainer.appendChild(itemDiv);
                    });

                    // Добавляем обработчики для изменения количества и удаления товара
                    document.querySelectorAll('.quantity-input').forEach(input => {
                        input.addEventListener('change', updateQuantity);
                    });

                    document.querySelectorAll('.quantity-btn').forEach(button => {
                        button.addEventListener('click', adjustQuantity);
                    });

                    document.querySelectorAll('.remove-button').forEach(button => {
                        button.addEventListener('click', removeItem);
                    });

                    // Рассчитываем общую сумму
                    updateTotal(cartData.cartItems);

                } else {
                    console.error("Неверная структура данных в cartItems");
                }
            } catch (error) {
                console.error("Ошибка при парсинге данных из localStorage:", error);
            }
        } else {
            console.log("Нет данных в localStorage по ключу 'cartItems'");
        }
    }

    // Обновление количества товара
    function updateQuantity(event) {
        const index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));

        // Изменяем количество
        cartData.cartItems[index].quantity = parseInt(event.target.value) || 1;

        // Если количество товара 0 или меньше, удаляем товар
        if (cartData.cartItems[index].quantity <= 0) {
            removeItem({ target: document.querySelector(`.remove-button[data-index="${index}"]`) });
        } else {
            // Сохраняем изменённые данные обратно в localStorage
            localStorage.setItem('cartItems', JSON.stringify(cartData));

            // Перезагружаем корзину
            loadCart();
        }
    }

    // Увеличение или уменьшение количества товара
    function adjustQuantity(event) {
        const index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));

        if (event.target.classList.contains('plus')) {
            cartData.cartItems[index].quantity++;
        } else if (event.target.classList.contains('minus') && cartData.cartItems[index].quantity > 0) {
            cartData.cartItems[index].quantity--;
        }

        // Если количество товара 0 или меньше, удаляем товар
        if (cartData.cartItems[index].quantity <= 0) {
            removeItem({ target: document.querySelector(`.remove-button[data-index="${index}"]`) });
        } else {
            // Сохраняем изменённые данные обратно в localStorage
            localStorage.setItem('cartItems', JSON.stringify(cartData));

            // Перезагружаем корзину
            loadCart();
        }
    }

    // Удаление товара из корзины
    function removeItem(event) {
        const index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));

        // Удаляем товар из массива
        cartData.cartItems.splice(index, 1);

        // Сохраняем изменённые данные обратно в localStorage
        localStorage.setItem('cartItems', JSON.stringify(cartData));

        // Перезагружаем корзину
        loadCart();
    }

    // Обновление общей суммы
    function updateTotal(cartItems) {
        let totalAmount = cartItems.reduce((total, item) => {
            let price = parseFloat(item.price) || 0; // Преобразуем цену в число
            return total + (price * item.quantity); // Умножаем цену на количество
        }, 0);

        // Обновляем значение общей суммы на странице внутри блока товаров
        document.getElementById('total-amount').innerText = `Общая сумма: ${totalAmount} ₽`;
    }

    // Загружаем корзину при загрузке страницы
    window.onload = loadCart;
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css"); // Подключение CSS
?>

<div class="checkout">
    <div class="section-title">
        <h2>Оформление заказа</h2>
        <p>Товары в корзине</p>
    </div>

    <div id="cart-items">
        <div class="product-checkout" id="product-checkout">
            <!-- Здесь будет вывод корзины -->
        </div>
        <div class="total" id="total-amount"><strong> Итог сумма: 0₽ </strong></div>
    </div>

    <!-- Кнопки "Назад" и "Оформить" -->
    <div class="button-group">
        <button onclick="history.back()" class="back-button">Назад</button>
        <button id="order-btn" class="order-button">Оформить заказ</button>
    </div>

    <!-- Модальное окно для формы оформления заказа -->
    <div id="order-form-container" class="modal">
        <div class="modal-content">
            <span class="close-button" id="close-modal">&times;</span>
            <form class="order-form" id="order-form">
                <h2>Ваши данные</h2>
                <label for="name">Имя:</label>
                <input type="text" id="name" name="name" required>
                <label for="phone">Телефон:</label>
                <input type="text" id="phone" name="phone" required>
                <label for="company">Компания:</label>
                <input type="text" id="company" name="company" required>
                <label for="inn">ИНН:</label>
                <input type="text" id="inn" name="inn" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="address">Адрес:</label>
                <textarea id="address" name="address" required></textarea>
                <button type="submit">Оформить заказ</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('order-form').addEventListener('submit', function (e) {
        e.preventDefault();  // Предотвращаем перезагрузку страницы

        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/send.php', true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById('contactForm').reset();
                document.getElementById('fileList').innerHTML = '';  // Очищаем список прикрепленных файлов
                alert('Ваше сообщение было успешно отправлено!');
            } else {
                alert('Произошла ошибка при отправке сообщения.');
            }
        };

        xhr.send(formData);  // Отправляем данные формы
    });

    // Функция для получения данных из localStorage и обработки их
    function loadCart() {
        let cartDataString = localStorage.getItem('cartItems');

        if (cartDataString) {
            try {
                let cartData = JSON.parse(cartDataString);

                if (cartData && Array.isArray(cartData.cartItems)) {
                    let cartItemsContainer = document.getElementById('product-checkout');
                    cartItemsContainer.innerHTML = '';

                    cartData.cartItems.forEach((item, index) => {
                        let itemDiv = document.createElement('div');
                        itemDiv.classList.add('cart-item');

                        // Проверка на наличие изображения
                        let imageHTML = getItemImage(item) ?
                                        `<div class="product-image"><img src="${getItemImage(item)}" alt="${item.name}"></div>` :
                                        `<div class="product-image"><div class="placeholder">Нет изображения</div></div>`;

                        // Если цена = 0, показываем "Цена под заказ"
                        let priceText = item.price == 0 || !item.price ? "под заказ" : `${item.price} ₽`;

                        // Основная цена товара (цена умноженная на количество)
                        let totalItemPrice = item.price == 0 || !item.price ? "под заказ" : `${(item.price * item.quantity).toFixed(2)} ₽`;

                        itemDiv.innerHTML = `
                            ${imageHTML}
                            <div class="product-info">
                                <span><strong>${item.name}</strong></span>
                                <span>Артикул: <strong>${item.sku}</strong></span>
                                <span class="price ${item.price == 0 ? 'price-soldout' : ''}">Цена за единицу: <strong>${priceText}</strong></span>
                                <span class="total-item-price">В сумме: <strong>${totalItemPrice}</strong></span>
                                <div class="quantity-control">
                                    <button class="quantity-btn minus" data-index="${index}">-</button>
                                    <input type="number" value="${item.quantity}" min="0" class="quantity-input" data-index="${index}" />
                                    <button class="quantity-btn plus" data-index="${index}">+</button>
                                </div>
                                <button class="remove-button" data-index="${index}">&times;</button>
                            </div>
                        `;
                        cartItemsContainer.appendChild(itemDiv);
                    });

                    // Обработчики событий
                    document.querySelectorAll('.quantity-input').forEach(input => {
                        input.addEventListener('change', updateQuantity);
                    });

                    document.querySelectorAll('.quantity-btn').forEach(button => {
                        button.addEventListener('click', adjustQuantity);
                    });

                    document.querySelectorAll('.remove-button').forEach(button => {
                        button.addEventListener('click', removeItem);
                    });

                    // Обновляем общую сумму
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

    // Функция для получения изображения товара
    function getItemImage(item) {
        if (item.image) {
            return item.image; // если картинка есть, то возвращаем её
        }

        // Если нет картинки в данных товара, пытаемся получить из инфоблока
        let infoblockImage = getInfoblockImage(item.sku);
        if (infoblockImage) {
            return infoblockImage; // возвращаем картинку из инфоблока
        }

        // Если изображения нет в товаре или инфоблоке, ставим заглушку
        return '/resources/img/production/0.png'; // путь к заглушке
    }

    // Имитация функции для получения изображения из инфоблока
    function getInfoblockImage(sku) {
        // Например, запрос из инфоблока по SKU или ID (можно заменить на настоящий запрос к API Битрикса)
        let imageURL = null;

        // Здесь можно сделать запрос к Битриксу, например, с помощью AJAX или API
        // Пример:
        // imageURL = getImageFromInfoblock(sku); // Получаем картинку из инфоблока

        return imageURL; // или null, если не нашли изображение
    }

    // Обновление общей суммы товаров в корзине
    function updateTotal(cartItems) {
        let totalAmount = cartItems.reduce((total, item) => {
            let price = parseFloat(item.price) || 0; // Преобразуем цену в число
            return total + (price * item.quantity); // Умножаем цену на количество
        }, 0);

        // Если цена товара 0 или не определена, показываем "Цена под заказ"
        if (totalAmount === 0) {
            document.getElementById('total-amount').innerText = `Итоговая сумма: под заказ`;
        } else {
            document.getElementById('total-amount').innerText = `Итоговая сумма: ${totalAmount.toFixed(2)}₽`;
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

        // Обновляем значение общей суммы на странице
        document.getElementById('total-amount').innerText = `Итоговая сумма: ${totalAmount}₽`;
    }

    // Открытие формы оформления заказа
    document.getElementById('order-btn').addEventListener('click', function() {
        document.querySelector('.modal').style.display = 'flex';
    });

    // Закрытие формы
    document.getElementById('close-modal').addEventListener('click', function() {
        document.querySelector('.modal').style.display = 'none';
    });

    // Закрытие формы при отправке
    document.getElementById('order-form').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Заказ оформлен!');
        document.querySelector('.modal').style.display = 'none';
    });

    // Загружаем корзину при загрузке страницы
    window.onload = loadCart;
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

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
        <div class="total" id="total-amount"><strong> Итоговая сумма: 0₽ </strong></div>
    </div>

    <div class="button-group">
        <button onclick="history.back()" class="back-button">Назад</button>
        <button id="order-btn" class="order-button">Оформить заказ</button>
    </div>

    <div id="order-form-container" class="modal">
        <div class="modal-content">
            <span class="close-button" id="close-modal">&times;</span>
            <form class="order-form" id="order-form">
                <h2>Ваши данные</h2>
                <!-- Поля для ввода данных -->
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
                <input type="hidden" id="cartData" name="cartData">

                <!-- Подключение hCaptcha -->
                <div class="h-captcha" data-sitekey="e65ad604-705a-4100-ab24-5cce1a363332"></div>

                <button type="submit" class="order-button" disabled>Оформить заказ</button>
            </form>
        </div>
    </div>
</div>

<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<script>
    document.getElementById('order-form').addEventListener('submit', function (e) {
        e.preventDefault(); // Предотвращаем перезагрузку страницы

        // Получаем данные корзины из localStorage и добавляем в форму
        const cartItems = localStorage.getItem('cartItems');
        document.getElementById('cartData').value = cartItems;

        // Получаем ответ hCaptcha
        const hcaptchaResponse = hcaptcha.getResponse();

        if (!hcaptchaResponse) {
            alert('Пожалуйста, подтвердите, что вы не робот.');
            return; // Если капча не пройдена, не отправляем форму
        }

        // Формируем данные для отправки на сервер
        var formData = new FormData(this);
        formData.append('h-captcha-response', hcaptchaResponse); // Добавляем ответ hCaptcha в форму

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'send_order.php', true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                if (response.status === 'success') {
                    alert(response.message); // Показываем сообщение о успешном заказе
                    localStorage.removeItem('cartItems'); // Очищаем корзину
                    window.location.href = '/'; // Перенаправляем на главную страницу
                } else {
                    alert(response.message); // Показываем ошибку
                }
            } else {
                alert('Произошла ошибка при оформлении заказа.');
            }
        };

        xhr.send(formData);
    });

    // Функция для обновления состояния кнопки отправки формы
    function updateOrderButtonState() {
        const name = document.getElementById('name').value;
        const phone = document.getElementById('phone').value;
        const company = document.getElementById('company').value;
        const inn = document.getElementById('inn').value;
        const email = document.getElementById('email').value;
        const address = document.getElementById('address').value;

        const isFormFilled = name && phone && company && inn && email && address;
        const isCaptchaValid = hcaptcha.getResponse() !== "";

        // Активируем кнопку, если все поля заполнены и капча пройдена
        document.querySelector('.order-button').disabled = !(isFormFilled && isCaptchaValid);
    }

    // Следим за изменениями в полях формы
    document.querySelectorAll('#order-form input, #order-form textarea').forEach(input => {
        input.addEventListener('input', updateOrderButtonState);
    });

    // Следим за ответом капчи
    hcaptcha.on('change', function() {
        updateOrderButtonState();
    });

    // Открытие формы оформления заказа
    document.getElementById('order-btn').addEventListener('click', function() {
        document.querySelector('.modal').style.display = 'flex';
    });

    // Закрытие формы
    document.getElementById('close-modal').addEventListener('click', function() {
        document.querySelector('.modal').style.display = 'none';
    });

    // Загружаем корзину при загрузке страницы
    window.onload = loadCart;

    function loadCart() {
        let cartDataString = localStorage.getItem('cartItems');
        if (cartDataString) {
            let cartData = JSON.parse(cartDataString);

            if (cartData && Array.isArray(cartData.cartItems)) {
                let cartItemsContainer = document.getElementById('product-checkout');
                cartItemsContainer.innerHTML = '';

                cartData.cartItems.forEach((item, index) => {
                    let itemDiv = document.createElement('div');
                    itemDiv.classList.add('cart-item');

                    let imageHTML = item.image ?
                        `<div class="product-image"><img src="${item.image}" alt="${item.name}"></div>` :
                        `<div class="product-image"><img src="/resources/img/production/0.png" alt="Нет изображения"></div>`;

                    let priceText = item.price == 0 || !item.price ? "под заказ" : `${item.price} ₽`;
                    let totalItemPrice = item.price == 0 || !item.price ? "под заказ" : `${(item.price * item.quantity).toFixed(2)} ₽`;

                    itemDiv.innerHTML = `
                        ${imageHTML}
                        <div class="product-info">
                            <span><strong>${item.name}</strong></span>
                            <span>Артикул: <strong>${item.article}</strong></span>
                            <span class="price">Цена за единицу: <strong>${priceText}</strong></span>
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

                document.querySelectorAll('.quantity-input').forEach(input => {
                    input.addEventListener('change', updateQuantity);
                });
                document.querySelectorAll('.quantity-btn').forEach(button => {
                    button.addEventListener('click', adjustQuantity);
                });
                document.querySelectorAll('.remove-button').forEach(button => {
                    button.addEventListener('click', removeItem);
                });
                updateTotal(cartData.cartItems);
            }
        }
    }

    function updateQuantity(event) {
        const index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));
        cartData.cartItems[index].quantity = parseInt(event.target.value) || 1;
        if (cartData.cartItems[index].quantity <= 0) {
            removeItem({ target: document.querySelector(`.remove-button[data-index="${index}"]`) });
        } else {
            localStorage.setItem('cartItems', JSON.stringify(cartData));
            loadCart();
        }
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

    // Изменение количества товара
    function adjustQuantity(event) {
        const index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));

        if (event.target.classList.contains('plus')) {
            cartData.cartItems[index].quantity++;
        } else if (event.target.classList.contains('minus') && cartData.cartItems[index].quantity > 0) {
            cartData.cartItems[index].quantity--;
        }

        if (cartData.cartItems[index].quantity <= 0) {
            removeItem({ target: document.querySelector(`.remove-button[data-index="${index}"]`) });
        } else {
            localStorage.setItem('cartItems', JSON.stringify(cartData));
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

    // Открытие формы оформления заказа
    document.getElementById('order-btn').addEventListener('click', function() {
        document.querySelector('.modal').style.display = 'flex';
    });

    // Закрытие формы
    document.getElementById('close-modal').addEventListener('click', function() {
        document.querySelector('.modal').style.display = 'none';
    });

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

    function updateTotal(cartItems) {
        let totalAmount = cartItems.reduce((total, item) => {
            let price = parseFloat(item.price) || 0;
            return total + (price * item.quantity);
        }, 0);
        document.getElementById('total-amount').innerText = `Итоговая сумма: ${totalAmount}₽`;
    }

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
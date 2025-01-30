<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css");
?>

<div class="checkout">
    <div class="section-title">
        <h2>Оформление заказа</h2>
        <p>Товары в корзине</p>
    </div>

    <div class="checkout-block">
        <div id="cart-items">
            <!-- Список товаров будет здесь -->
        </div>

        <div class="total" id="total-amount"><strong>Итоговая сумма: 0 ₽</strong></div>

        <div class="button-group">
            <button onclick="history.back()" class="back-button">Назад</button>
            <button id="order-btn" class="order-button">Оформить заказ</button>
        </div>

        <div id="order-form-container" class="modal">
            <div class="modal-content">
                <span class="close-button" id="close-modal">&times;</span>
                <form class="order-form" id="order-form" action="/resources/src/send_order.php" method="POST">
                    <h2>Ваши данные</h2>
                    <label for="name">Имя:</label>
                    <input type="text" id="name" name="name" required>
                    <label for="phone">Телефон:</label>
                    <input type="text" id="phone" name="phone" required>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <label for="address">Адрес доставки:</label>
                    <textarea id="address" name="address" required></textarea>
                    <input type="hidden" id="cartData" name="cartData">
                    <button type="submit">Отправить заказ</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('order-form').addEventListener('submit', function (e) {
        e.preventDefault(); // Предотвращаем перезагрузку страницы

        // Получаем данные корзины из localStorage и добавляем в форму
        const cartItems = localStorage.getItem('cartItems');
        document.getElementById('cartData').value = cartItems;

        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/resources/src/send_order.php', true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                alert('Заказ успешно оформлен!');
                localStorage.removeItem('cartItems'); // Очищаем корзину
                window.location.href = '/';
            } else {
                alert('Произошла ошибка при оформлении заказа.');
            }
        };

        xhr.send(formData);
    });

    // Загрузка корзины при открытии страницы
    function loadCart() {
        const cartData = JSON.parse(localStorage.getItem('cartItems')) || { cartItems: [] };
        const cartItemsContainer = document.getElementById('cart-items');
        cartItemsContainer.innerHTML = '';

        let totalSum = 0;

        cartData.cartItems.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            totalSum += itemTotal;

            const cartItemHTML = `
                <div class="cart-item">
                    <a href="/product-detail/${item.id}" class="cart-item-link">
                        <div class="product-image">
                            <img src="${item.image || '/resources/img/production/0.png'}" alt="${item.name}">
                        </div>
                        <div class="product-info">
                            <span><strong>${item.name}</strong></span>
                            <span>Артикул: <strong>${item.article}</strong></span>
                            <span class="price">Цена за единицу: <strong>${item.price} ₽</strong></span>
                            <span class="total-item-price">В сумме: <strong>${itemTotal} ₽</strong></span>
                        </div>
                    </a>
                    <div class="quantity-control">
                        <button class="quantity-btn minus" data-index="${index}">-</button>
                        <input type="number" value="${item.quantity}" min="1" class="quantity-input" data-index="${index}">
                        <button class="quantity-btn plus" data-index="${index}">+</button>
                    </div>
                    <button class="remove-button" data-index="${index}">&times;</button>
                </div>
            `;

            cartItemsContainer.innerHTML += cartItemHTML;
        });

        document.getElementById('total-amount').innerText = `Итоговая сумма: ${totalSum} ₽`;
    }

    // Обновление количества товара
    function updateQuantity(event) {
        const index = event.target.getAttribute('data-index');
        const cartData = JSON.parse(localStorage.getItem('cartItems'));
        cartData.cartItems[index].quantity = parseInt(event.target.value) || 1;
        localStorage.setItem('cartItems', JSON.stringify(cartData));
        loadCart();
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
            document.getElementById('total-amount').innerText = `Итоговая сумма: ${totalAmount.toFixed(2)} ₽`;
        }
    }

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

    // Удаление товара
    function removeItem(event) {
        const index = event.target.getAttribute('data-index');
        const cartData = JSON.parse(localStorage.getItem('cartItems'));
        cartData.cartItems.splice(index, 1);
        localStorage.setItem('cartItems', JSON.stringify(cartData));
        loadCart();
    }

    function updateTotal(cartItems) {
        let totalAmount = cartItems.reduce((total, item) => {
            let price = parseFloat(item.price) || 0;
            return total + (price * item.quantity);
        }, 0);
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

    // Открытие модального окна
    document.getElementById('order-btn').addEventListener('click', () => {
        document.getElementById('order-form-container').style.display = 'flex';
        document.getElementById('cartData').value = localStorage.getItem('cartItems');
    });

    // Закрытие модального окна
    document.getElementById('close-modal').addEventListener('click', () => {
        document.getElementById('order-form-container').style.display = 'none';
    });

    // Загружаем корзину при загрузке страницы
    window.onload = loadCart;
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

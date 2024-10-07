// Функция для выбора всех товаров
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
    checkboxes.forEach(item => {
        item.checked = checkbox.checked;
    });
}

// Функция перехода на каталог по секциям
function redirectToSection(sectionId) {
    window.location.href = '/catalog/catalog.php?SECTION_ID=' + sectionId;
}

//************ Корзина товаров **************//
// Добавление товара в корзину и сохранение в куки
document.querySelectorAll('.catalog-item-add-to-cart').forEach(function (button) {
    button.addEventListener('click', function () {
        var productId = this.getAttribute('data-id');
        addToCart(productId);
    });
});

// Функция добавления товара в корзину и сохранение в куки
function addToCart(productId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_to_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var productData = JSON.parse(xhr.responseText);

            var cartItems = getCartItemsFromCookies();

            // Проверяем, есть ли товар уже в корзине
            var existingItem = cartItems.find(item => item.id === productData.id);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                // Добавляем новый товар
                cartItems.push({
                    id: productData.id,
                    name: productData.name || "Без названия",  // Название товара
                    article: productData.article || "",  // Артикул
                    price: productData.price || 0,  // Цена товара
                    picture: productData.picture || productData.section_picture,  // Картинка товара или раздела
                    quantity: 1
                });
            }

            // Сохраняем обновленную корзину в куки
            saveCartItemsToCookies(cartItems);

            // Обновляем корзину
            updateCart(cartItems);
        }
    };
    xhr.send('id=' + productId);
}

// Получение товаров из куки
function getCartItemsFromCookies() {
    var cart = Cookies.get('cart');
    return cart ? JSON.parse(cart) : [];
}

// Сохранение товаров в куки
function saveCartItemsToCookies(cartItems) {
    Cookies.set('cart', JSON.stringify(cartItems), { expires: 7 });
}

// Обновление корзины на странице
function updateCart(cartItems) {
    var basketItems = document.getElementById('basket-items');
    basketItems.innerHTML = '';

    var totalPrice = 0;

    cartItems.forEach(function (item) {
        var row = document.createElement('tr');
        row.innerHTML = `
            <td><img src="${item.picture}" alt="${item.name}" style="width: 50px; height: 50px;"></td>
            <td>${item.name}</td>
            <td><input type="number" value="${item.quantity}" min="1" class="quantity" data-id="${item.id}"></td>
            <td>${item.price} руб.</td>
            <td>${item.price * item.quantity} руб.</td>
        `;
        basketItems.appendChild(row);

        totalPrice += item.price * item.quantity;
    });

    document.getElementById('total-price').textContent = totalPrice + ' руб.';

    // Добавляем обработчики для изменения количества товара
    document.querySelectorAll('.quantity').forEach(function (input) {
        input.addEventListener('change', function () {
            var productId = this.getAttribute('data-id');
            var quantity = this.value;
            updateQuantity(productId, quantity);
        });
    });
}

// Обновление количества товара и сохранение изменений в куки
function updateQuantity(productId, quantity) {
    var cartItems = getCartItemsFromCookies();

    cartItems.forEach(function (item) {
        if (item.id === productId) {
            item.quantity = parseInt(quantity);
        }
    });

    saveCartItemsToCookies(cartItems);
    updateCart(cartItems);
}

// Очистка корзины
document.getElementById('clear-cart').addEventListener('click', function () {
    Cookies.remove('cart');
    updateCart([]);
});

// Оформление заказа (редирект на страницу оформления)
document.getElementById('checkout').addEventListener('click', function () {
    window.location.href = '/checkout/';
});

// При загрузке страницы загружаем корзину из куков
window.onload = function () {
    var cartItems = getCartItemsFromCookies();
    updateCart(cartItems);
};
// Функции плавующего меню
function toggleMenu() {
    var nav = document.getElementById('mainNav');
    nav.classList.toggle('menu-open');
}

window.onscroll = function() {stickyHeader()};

var header = document.getElementById("siteHeader");
var sticky = header.offsetTop;

function stickyHeader() {
    if (window.pageYOffset > sticky) {
        header.classList.add("fixed");
    } else {
        header.classList.remove("fixed");
    }
}

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
// Добавление товара в корзину
document.querySelectorAll('.catalog-item-add-to-cart').forEach(function (button) {
    button.addEventListener('click', function () {
        var productId = this.getAttribute('data-id');
        addToCart(productId);
    });
});

// Добавление товара в корзину (AJAX)
function addToCart(productId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_to_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            updateCart(JSON.parse(xhr.responseText));
        }
    };
    xhr.send('id=' + productId);
}

// Обновление корзины
function updateCart(basketData) {
    var basketItems = document.getElementById('basket-items');
    basketItems.innerHTML = '';

    var totalPrice = 0;

    basketData.forEach(function (item) {
        var row = document.createElement('div');
        row.classList.add('basket-item');
        row.innerHTML = `
            <div class="item-details">
                <img src="${item.picture}" alt="${item.name}" class="item-picture">
                <h4 class="item-name">${item.name}</h4>
                <p class="item-price">Цена: ${item.price} руб.</p>
                <p class="item-quantity">Количество: <input type="number" value="${item.quantity}" min="1" class="quantity" data-id="${item.id}"></p>
                <p class="item-total-price">Стоимость: ${item.price * item.quantity} руб.</p>
            </div>
        `;
        basketItems.appendChild(row);

        totalPrice += item.price * item.quantity;
    });

    document.getElementById('total-price').textContent = totalPrice;

    // Изменение количества товара
    document.querySelectorAll('.quantity').forEach(function (input) {
        input.addEventListener('change', function () {
            var productId = this.getAttribute('data-id');
            var quantity = this.value;
            updateQuantity(productId, quantity);
        });
    });
}

// Обновление количества товара (AJAX)
function updateQuantity(productId, quantity) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_quantity.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            updateCart(JSON.parse(xhr.responseText));
        }
    };
    xhr.send('id=' + productId + '&quantity=' + quantity);
}

// Очистка корзины
document.getElementById('clear-cart').addEventListener('click', function () {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'clear_cart.php', true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            updateCart([]);
        }
    };
    xhr.send();
});

// Оформление заказа
document.getElementById('checkout').addEventListener('click', function () {
    window.location.href = '/checkout/';
});
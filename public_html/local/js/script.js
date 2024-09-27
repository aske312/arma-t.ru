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

// Функция для редиректа на страницу категории при клике на категорию
//function redirectToSection(sectionId) {
//    window.location.href = '/catalog/catalog.php?SECTION_ID=${sectionId}';
//}

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
function addToCart(productId, article, price) {
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
        var row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td><input type="number" value="${item.quantity}" min="1" class="quantity" data-id="${item.id}"></td>
            <td>${item.price}</td>
            <td>${item.total}</td>
        `;
        basketItems.appendChild(row);

        totalPrice += item.total;
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


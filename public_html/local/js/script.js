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

//*** КОРЗИНА ***//
document.querySelectorAll('.catalog-item-add-to-cart').forEach(function (button) {
    button.addEventListener('click', function () {
        var productId = this.getAttribute('data-id');
        addToCart(productId);
    });
});

function addToCart(productId) {
    // Получаем текущие товары из куки
    var cart = JSON.parse(getCookie('cart') || '[]');

    // Проверяем, есть ли товар уже в корзине
    if (!cart.includes(productId)) {
        cart.push(productId); // Добавляем ID в корзину
        setCookie('cart', JSON.stringify(cart), 1); // Сохраняем куки на 7 дней
        updateCartCounter();
    }
}

function updateCartCounter() {
    var cart = JSON.parse(getCookie('cart') || '[]');
    var counter = cart.length;
    document.getElementById('cart-counter').innerText = counter;
}

// Функция для получения куки
function getCookie(name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
}

// Функция для установки куки
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

// Обновляем счетчик сразу при загрузке страницы
updateCartCounter();
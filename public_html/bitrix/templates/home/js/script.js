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
// Функция для добавления товара в корзину
function addToCart(productId, productArticle, productPrice, productName, productImage) {
    let cart = getCartFromCookies();

    // Проверяем, есть ли товар в корзине
    let item = cart.find(item => item.id === productId);
    if (item) {
        // Увеличиваем количество, если товар уже есть
        item.quantity++;
    } else {
        // Добавляем новый товар
        cart.push({
            id: productId,
            article: productArticle,
            price: productPrice || 0, // Если цена 0 или отсутствует
            name: productName,
            image: productImage || 'default-image.jpg', // Если нет картинки
            quantity: 1
        });
    }

    // Сохраняем корзину в куки
    saveCartToCookies(cart);

    // Обновляем отображение корзины
    updateCartUI();
}

// Получение корзины из куки
function getCartFromCookies() {
    let cart = document.cookie.split('; ').find(row => row.startsWith('cart='));
    return cart ? JSON.parse(decodeURIComponent(cart.split('=')[1])) : [];
}

// Сохранение корзины в куки
function saveCartToCookies(cart) {
    document.cookie = 'cart=' + encodeURIComponent(JSON.stringify(cart)) + '; path=/; max-age=31536000'; // 1 год
}

// Обновление UI корзины
function updateCartUI() {
    let cart = getCartFromCookies();
    let cartCount = cart.reduce((count, item) => count + item.quantity, 0);
    let cartButton = document.getElementById('cart-button');
    let cartTotalPrice = cart.reduce((total, item) => total + (item.price * item.quantity), 0);

    if (cartCount > 0) {
        cartButton.classList.remove('hidden');
        document.getElementById('cart-count').textContent = cartCount;
        document.getElementById('cart-total-price').textContent = cartTotalPrice;
    } else {
        cartButton.classList.add('hidden');
    }
}

// Отображение товаров в выпадающем списке корзины
function showCartItems() {
    let cart = getCartFromCookies();
    let cartItems = document.getElementById('cart-items');
    cartItems.innerHTML = '';

    cart.forEach(item => {
        let listItem = document.createElement('li');
        listItem.innerHTML = `
            <img src="${item.image}" alt="${item.name}" width="50" />
            <span>${item.name}</span> x${item.quantity} - ${item.price * item.quantity} руб.
        `;
        cartItems.appendChild(listItem);
    });
}

// Показ/скрытие выпадающего списка корзины
document.getElementById('cart-button').addEventListener('click', function () {
    let cartDropdown = document.getElementById('cart-dropdown');
    cartDropdown.classList.toggle('hidden');
    showCartItems();
});

// Пример кнопки добавления товара в корзину
document.querySelectorAll('.catalog-item-add-to-cart').forEach(function (button) {
    button.addEventListener('click', function () {
        let productId = this.getAttribute('data-id');
        let productArticle = this.getAttribute('data-article');
        let productPrice = this.getAttribute('data-price');
        let productName = this.getAttribute('data-name');
        let productImage = this.getAttribute('data-image');

        addToCart(productId, productArticle, productPrice, productName, productImage);
    });
});

// Инициализация корзины при загрузке страницы
document.addEventListener('DOMContentLoaded', updateCartUI);
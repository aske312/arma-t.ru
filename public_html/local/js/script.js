//***** header.php *****//
function toggleMenu() {
    var nav = document.getElementById('mainNav');
    nav.classList.toggle('menu-open');
}

//***** index.php *****//
let slideIndex = 0; // Изначальный индекс слайда
let slides = document.getElementsByClassName('slide'); // Получаем все слайды
let dots = document.getElementsByClassName('dot'); // Получаем все точки

// Функция для показа слайдов
function showSlides() {
    // Скрываем все слайды
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = 'none';
    }

    // Убираем активные классы у всех точек
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(' active', '');
    }

    // Увеличиваем индекс слайда
    slideIndex++;

    // Если индекс выходит за количество слайдов, возвращаемся к первому
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }

    // Показываем текущий слайд и активируем соответствующую точку
    slides[slideIndex - 1].style.display = 'block';
    dots[slideIndex - 1].className += ' active';
}

// Функция автоматического переключения слайдов
function startAutoSlide() {
    slideInterval = setInterval(function() {
        showSlides();
    }, 5000); // Интервал переключения - 5 секунд
}

// Остановка автоматического переключения слайдов
function stopAutoSlide() {
    clearInterval(slideInterval);
}

// Переключение на конкретный слайд при клике на точку
function currentSlide(n) {
    // Останавливаем автоматическое переключение
    stopAutoSlide();

    // Устанавливаем индекс слайда
    slideIndex = n;

    // Показываем выбранный слайд
    showSlides();

    // Перезапускаем автоматическое переключение слайдов
    startAutoSlide();
}

// Инициализация слайдера при загрузке страницы
window.onload = function() {
    // Показываем первый слайд
    showSlides();

    // Запускаем автоматическое переключение слайдов
    startAutoSlide();
};

// Добавляем события клика для точек (dots)
for (let i = 0; i < dots.length; i++) {
    dots[i].addEventListener('click', function() {
        currentSlide(i + 1);  // При клике переходим на слайд с индексом i
    });
}

//***** index.php catalog *****//
// Функция перехода на каталог по секциям
function redirectToSection(sectionId) {
    window.location.href = '/catalog/catalog.php?SECTION_ID=' + sectionId;
}

//***** catalog.php *****//
document.getElementById("catalog-button").addEventListener("click", function() {
    var catalogMenu = document.getElementById("catalog-menu");
    if (catalogMenu.style.display === "block") {
        catalogMenu.style.display = "none";
    } else {
        catalogMenu.style.display = "block";
    }
});

// Функция перехода на детальную страницу
function redirectToDetail(sectionId, elementId) {
    window.location.href = '/catalog/detail.php?SECTION_ID=' + sectionId + '&ID=' + elementId;
}

// Функция применения фильтра
function applyFilter(property, value) {
    console.log(`Применен фильтр: ${property} = ${value}`);
    // Здесь можно добавить логику для фильтрации элементов каталога на странице
}

// Функция выбора/отмены всех товаров
function toggleSelectAll(checkbox) {
    var checkboxes = document.querySelectorAll(".catalog-item-checkbox");
    checkboxes.forEach(function(cb) {
        cb.checked = checkbox.checked;
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const categoryButtons = document.querySelectorAll('.catalog-category-btn');
    const catalogItems = document.querySelector('.catalog-items');
    const sectionTitle = document.getElementById('section-title');

    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.dataset.sectionId;

            // Обновление заголовка раздела
            sectionTitle.textContent = this.textContent;

            // Очистка текущих товаров
            catalogItems.innerHTML = '';

            // AJAX-запрос для получения товаров выбранной категории
            fetch('index.php?SECTION_ID=' + sectionId)
                .then(response => response.text())
                .then(data => {
                    catalogItems.innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        });
    });
});

// Корзина
let cart = [];

function toggleCart() {
    const cartDropdown = document.getElementById("cart");
    cartDropdown.classList.toggle("hidden");
}

function addToCart(itemId, itemArticul, itemPrice) {
    const existingItem = cart.find(item => item.id === itemId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ id: itemId, articul: itemArticul, price: itemPrice, quantity: 1 });
    }
    updateCart();
}

function updateCart() {
    const cartCount = document.getElementById("cart-count");
    const cartItems = document.getElementById("cart-items");
    const cartTotalPrice = document.getElementById("cart-total-price");

    cartCount.innerText = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartItems.innerHTML = cart.map(item => `
        <li>
            ${item.articul} — ${item.quantity} шт. — ${item.price * item.quantity} руб.
            <button onclick="changeQuantity(${item.id}, -1)">-</button>
            <button onclick="changeQuantity(${item.id}, 1)">+</button>
        </li>
    `).join('');

    cartTotalPrice.innerText = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

function changeQuantity(itemId, delta) {
    const item = cart.find(item => item.id === itemId);
    if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) {
            cart = cart.filter(item => item.id !== itemId);
        }
        updateCart();
    }
}

function clearCart() {
    cart = [];
    updateCart();
}

function checkout() {
    alert("Оформление заказа");
    // здесь будет логика для оформления заказа
}

function addSelectedToCart() {
    const checkboxes = document.querySelectorAll(".catalog-item-checkbox:checked");
    checkboxes.forEach(checkbox => {
        const itemId = checkbox.getAttribute("data-id");
        const itemArticul = checkbox.getAttribute("data-articul");
        const itemPrice = parseFloat(checkbox.getAttribute("data-price"));
        addToCart(itemId, itemArticul, itemPrice);
    });
}

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll(".catalog-item-checkbox");
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
}
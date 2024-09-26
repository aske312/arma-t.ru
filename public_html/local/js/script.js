//***** header.php *****//
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

//// Корзина catalog.php
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

// Переключение отображения корзины
function toggleBasketDropdown() {
    const basketDropdown = document.getElementById('basketDropdown');
    basketDropdown.style.display = basketDropdown.style.display === 'none' ? 'block' : 'none';
}

// Функция для добавления выбранных товаров в корзину
document.querySelector('.catalog-add-all').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.catalog-item-checkbox:checked');
    checkboxes.forEach(function(checkbox) {
        const itemId = checkbox.id.replace('item-', '');
        addToCart(itemId);
    });
});

// Функция для выделения всех товаров
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = source.checked);
}

// Функция для добавления товара в корзину
function addToCart(itemId) {
    const basket = getBasket();
    const item = basket.find(i => i.id === itemId);

    if (item) {
        item.count++;
    } else {
        basket.push({ id: itemId, count: 1 });
    }

    saveBasket(basket);
    updateBasketUI();
}

// Получение корзины из LocalStorage
function getBasket() {
    const basket = localStorage.getItem('basket');
    return basket ? JSON.parse(basket) : [];
}

// Сохранение корзины в LocalStorage
function saveBasket(basket) {
    localStorage.setItem('basket', JSON.stringify(basket));
}

// Очистка корзины
function clearBasket() {
    localStorage.removeItem('basket');
    updateBasketUI();
}

// Обновление пользовательского интерфейса корзины
function updateBasketUI() {
    const basket = getBasket();
    const basketButton = document.getElementById('basketButton');
    const basketCount = document.getElementById('basketCount');
    const basketItems = document.getElementById('basketItems');
    const basketTotal = document.getElementById('basketTotal');

    if (basket.length === 0) {
        basketButton.style.display = 'none';
        basketDropdown.style.display = 'none';
    } else {
        basketButton.style.display = 'block';
        basketCount.innerText = basket.reduce((sum, item) => sum + item.count, 0);
        basketItems.innerHTML = basket.map(item => `
            <div>
                Товар: ${item.id}, Количество: ${item.count}
                <button onclick="removeFromCart(${item.id})">-</button>
                <button onclick="increaseCart(${item.id})">+</button>
            </div>
        `).join('');
        const totalPrice = basket.reduce((sum, item) => sum + (getItemPrice(item.id) * item.count), 0);
        basketTotal.innerText = 'Общая цена: ' + totalPrice + ' руб.';
    }
}

// Увеличение количества товаров в корзине
function increaseCart(itemId) {
    const basket = getBasket();
    const item = basket.find(i => i.id === itemId);
    if (item) {
        item.count++;
        saveBasket(basket);
        updateBasketUI();
    }
}

// Уменьшение количества товаров в корзине
function removeFromCart(itemId) {
    const basket = getBasket();
    const itemIndex = basket.findIndex(i => i.id === itemId);
    if (itemIndex > -1) {
        if (basket[itemIndex].count > 1) {
            basket[itemIndex].count--;
        } else {
            basket.splice(itemIndex, 1);
        }
        saveBasket(basket);
        updateBasketUI();
    }
}

// Получение цены товара (это просто пример, вместо этого нужно запросить цену из данных товара)
function getItemPrice(itemId) {
    return 100; // заменить на реальную цену товара
}

// Оформление заказа (переход на страницу оформления заказа)
function checkout() {
    alert('Переход на страницу оформления заказа');
    // Реализуйте переход на страницу оформления заказа
}

// Инициализация корзины при загрузке страницы
document.addEventListener('DOMContentLoaded', updateBasketUI);

// КОРЗИНА ТОВАРВ catalog.php
// Переключение отображения корзины
function toggleBasketDropdown() {
    const basketDropdown = document.getElementById('basketDropdown');
    basketDropdown.style.display = basketDropdown.style.display === 'none' ? 'block' : 'none';
}

// Функция для добавления товара в корзину
function addToCart(itemId) {
    const basket = getBasket();
    const item = basket.find(i => i.id === itemId);

    if (item) {
        item.count++;
    } else {
        basket.push({ id: itemId, count: 1 });
    }

    saveBasket(basket);
    updateBasketUI();
}

// Получение корзины из LocalStorage
function getBasket() {
    const basket = localStorage.getItem('basket');
    return basket ? JSON.parse(basket) : [];
}

// Сохранение корзины в LocalStorage
function saveBasket(basket) {
    localStorage.setItem('basket', JSON.stringify(basket));
}

// Очистка корзины
function clearBasket() {
    localStorage.removeItem('basket');
    updateBasketUI();
}

// Обновление пользовательского интерфейса корзины
function updateBasketUI() {
    const basket = getBasket();
    const basketButton = document.getElementById('basketButton');
    const basketCount = document.getElementById('basketCount');
    const basketItems = document.getElementById('basketItems');
    const basketTotal = document.getElementById('basketTotal');

    if (basket.length === 0) {
        basketButton.style.display = 'none';
        basketDropdown.style.display = 'none';
    } else {
        basketButton.style.display = 'block';
        basketCount.innerText = basket.reduce((sum, item) => sum + item.count, 0);
        basketItems.innerHTML = basket.map(item => `
            <div>
                Товар: ${item.id}, Количество: ${item.count}
                <button onclick="removeFromCart(${item.id})">-</button>
                <button onclick="increaseCart(${item.id})">+</button>
            </div>
        `).join('');
        const totalPrice = basket.reduce((sum, item) => sum + (getItemPrice(item.id) * item.count), 0);
        basketTotal.innerText = 'Общая цена: ' + totalPrice + ' руб.';
    }
}

// Увеличение количества товаров в корзине
function increaseCart(itemId) {
    const basket = getBasket();
    const item = basket.find(i => i.id === itemId);
    if (item) {
        item.count++;
        saveBasket(basket);
        updateBasketUI();
    }
}

// Уменьшение количества товаров в корзине
function removeFromCart(itemId) {
    const basket = getBasket();
    const itemIndex = basket.findIndex(i => i.id === itemId);
    if (itemIndex > -1) {
        if (basket[itemIndex].count > 1) {
            basket[itemIndex].count--;
        } else {
            basket.splice(itemIndex, 1);
        }
        saveBasket(basket);
        updateBasketUI();
    }
}

// Получение цены товара (это просто пример, вместо этого нужно запросить цену из данных товара)
function getItemPrice(itemId) {
    return 100; // заменить на реальную цену товара
}

// Оформление заказа (переход на страницу оформления заказа)
function checkout() {
    alert('Переход на страницу оформления заказа');
    // Реализуйте переход на страницу оформления заказа
}

// Инициализация корзины при загрузке страницы
document.addEventListener('DOMContentLoaded', updateBasketUI);

// Функция для добавления выбранных товаров в корзину
document.querySelector('.catalog-add-all').addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.catalog-item-checkbox:checked');
    checkboxes.forEach(function(checkbox) {
        const itemId = checkbox.id.replace('item-', '');
        addToCart(itemId);
    });
});

// Функция для выделения всех товаров
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = source.checked);
}
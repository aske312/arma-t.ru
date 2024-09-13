//***** header.php *****//
function toggleMenu() {
    var nav = document.getElementById('mainNav');
    nav.classList.toggle('menu-open');
}

//***** index.php *****//
let slideIndex = 0; // Изначально показываем первый слайд
let slides = document.getElementsByClassName('slide'); // Все слайды
let dots = document.getElementsByClassName('dot'); // Все точки

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

    // Автоматическое переключение слайдов через 5 секунд
    setTimeout(showSlides, 5000);
}

// Инициализация слайдера при загрузке страницы
window.onload = function() {
    showSlides(); // Запускаем слайдер сразу
};

// Функция для ручного переключения слайдов через точки
function currentSlide(n) {
    slideIndex = n; // Устанавливаем текущий индекс слайда
    // Сбрасываем таймер автопереключения и показываем выбранный слайд
    clearTimeout(slideInterval);
    showSlides();
}

// Добавляем события для кликов по точкам
for (let i = 0; i < dots.length; i++) {
    dots[i].addEventListener('click', function() {
        currentSlide(i + 1); // При клике переходим на нужный слайд
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
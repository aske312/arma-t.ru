//***** header.php *****//
function toggleMenu() {
    var nav = document.getElementById('mainNav');
    nav.classList.toggle('menu-open');
}

//***** index.php *****//
let slideIndex = 0;
let slides = document.getElementsByClassName("slide");
let dots = document.getElementsByClassName("dot");

// Функция для показа слайдов
function showSlides() {
    // Скрываем все слайды
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";  // Скрываем каждый слайд
    }

    // Убираем активные классы у всех точек
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", ""); // Убираем класс 'active' у всех точек
    }

    // Увеличиваем индекс слайда
    slideIndex++;

    // Если индекс слайда больше количества слайдов — обнуляем его
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }

    // Показываем текущий слайд и активируем соответствующую точку
    slides[slideIndex - 1].style.display = "block";  // Отображаем нужный слайд
    dots[slideIndex - 1].className += " active";  // Делаем точку активной
}

// Инициализация первого слайда
showSlides();

// Автоматическое переключение каждые 5 секунд
let slideInterval = setInterval(showSlides, 5000);

// Переключение на конкретный слайд по клику на точку
function currentSlide(n) {
    clearInterval(slideInterval);  // Останавливаем автоматическое переключение
    slideIndex = n;  // Устанавливаем текущий индекс слайда
    showSlides();  // Показываем выбранный слайд
    slideInterval = setInterval(showSlides, 5000);  // Перезапускаем автоматическое переключение
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
//***** header.php *****//
function toggleMenu() {
    var nav = document.getElementById('mainNav');
    nav.classList.toggle('menu-open');
}

//***** index.php *****//
let slideIndex = 1;
let slides = document.getElementsByClassName("slide");
let dots = document.getElementsByClassName("dot");

function showSlides(n) {
    // Если слайдов больше чем slideIndex, начать с первого слайда
    if (n > slides.length) {
        slideIndex = 1;
    }
    // Если меньше, начать с последнего
    if (n < 1) {
        slideIndex = slides.length;
    }

    // Скрываем все слайды
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }

    // Убираем активные точки
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }

    // Показываем текущий слайд и активируем точку
    slides[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].className += " active";
}

// Инициализация
showSlides(slideIndex);

// Функция для перехода на следующий слайд
function nextSlide() {
    showSlides(slideIndex += 1);
}

// Функция для перехода на определённый слайд
function currentSlide(n) {
    showSlides(slideIndex = n);
}

// Автоматическая смена слайдов
let slideInterval = setInterval(nextSlide, 5000);

// Остановить автоматическую смену слайдов при клике на точку
for (let i = 0; i < dots.length; i++) {
    dots[i].addEventListener('click', function() {
        clearInterval(slideInterval);
        currentSlide(i + 1);
        slideInterval = setInterval(nextSlide, 5000);  // Перезапуск таймера
    });
}

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
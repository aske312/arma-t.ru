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
function showSlides(n) {
    if (n >= slides.length) {
        slideIndex = 0; // Если дошли до конца, возвращаемся к первому слайду
    } else if (n < 0) {
        slideIndex = slides.length - 1; // Если индекс меньше 0, показываем последний слайд
    } else {
        slideIndex = n; // Устанавливаем текущий индекс
    }

    // Скрываем все слайды
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }

    // Убираем активные классы у всех точек
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }

    // Показываем текущий слайд и активируем соответствующую точку
    slides[slideIndex].style.display = "block";
    dots[slideIndex].className += " active";
}

// Инициализация первого слайда
showSlides(slideIndex);

// Автоматическое переключение каждые 5 секунд
let slideInterval = setInterval(function() {
    showSlides(slideIndex + 1); // Переходим на следующий слайд
}, 5000);

// Переключение на конкретный слайд по клику на точку
function currentSlide(n) {
    clearInterval(slideInterval); // Останавливаем автоматическое переключение
    showSlides(n); // Показываем выбранный слайд
    slideInterval = setInterval(function() {
        showSlides(slideIndex + 1); // Перезапускаем автоматическое переключение
    }, 5000);
}

// Добавляем события клика для точек
for (let i = 0; i < dots.length; i++) {
    dots[i].addEventListener('click', function() {
        currentSlide(i); // Переход на слайд с индексом i
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
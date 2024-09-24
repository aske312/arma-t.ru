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

document.getElementById('contactForm').addEventListener('submit', function (e) {
    e.preventDefault();  // Предотвращаем перезагрузку страницы

    var formData = new FormData(this);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'send.php', true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('formMessage').style.display = 'block';
            document.getElementById('formErrorMessage').style.display = 'none';
        } else {
            document.getElementById('formErrorMessage').style.display = 'block';
            document.getElementById('formMessage').style.display = 'none';
        }
    };

    xhr.send(formData);  // Отправляем данные формы
});

// Обновление счётчика файлов при выборе
document.getElementById('files').addEventListener('change', function () {
    var fileCount = this.files.length;
    if (fileCount > 3) {
        alert("Вы можете загрузить не более 3 файлов.");
        this.value = '';  // Очищаем поле
    } else {
        document.getElementById('fileCount').textContent = "Файлы: " + fileCount;
    }
});

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
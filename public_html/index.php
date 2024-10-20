<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->SetTitle("Главная");

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Iblock;

Loader::includeModule('iblock');

Asset::getInstance()->addCss("/local/css/home.css"); //css
Asset::getInstance()->addJs("/local/js/script.js"); //js

$sectionsFilter = [
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'ACTIVE' => 'Y',
    'GLOBAL_ACTIVE' => 'Y',
];
$arSelect = ['ID', 'NAME', 'PICTURE'];
$sections = CIBlockSection::GetList(['SORT' => 'ASC'], $sectionsFilter, false, $arSelect);

$arResult['SECTIONS'] = [];
while ($section = $sections->Fetch()) {
    $arResult['SECTIONS'][] = $section;
}

// Подключаем модуль инфоблоков
if (CModule::IncludeModule('iblock')) {
    // Параметры инфоблока
    $arSelect = ["ID", "NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT"];
    $arFilter = ["IBLOCK_ID" => 2, "ACTIVE" => "Y"]; // slider = 3

    $res = CIBlockElement::GetList(["SORT" => "ASC"], $arFilter, false, false, $arSelect);
    while ($arItem = $res->GetNext()) {
        $imgPath = CFile::GetPath($arItem["PREVIEW_PICTURE"]);
        $slides[] = [
            "TEXT" => $arItem["PREVIEW_TEXT"],
            "IMG" => $imgPath,
        ];
    }
}
?>

<div class="section section1">
    <div class="slider-wrapper">
        <div class="slider-container">
            <div class="slider">
                <div class="slides">
                    <?php foreach ($slides as $slide): ?>
                    <div class="slide">
                        <img alt="Slide" src="<?= $slide['IMG'] ?>">
                        <div class="slide-text">
                            <?= $slide['TEXT'] ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="dots">
                    <?php foreach ($slides as $index => $slide): ?>
                    <span class="dot" onclick="currentSlide(<?= $index ?>)"></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- -->

<div class="section section2" id="Company">
	<div class="container">
		<div class="content">
			<div class="image">
                <img alt="Image" src="/local/img/block/312asd.png">
			</div>
			<div class="text">
				<p>АРМА-Т - специализируется на продаже запорной арматуры. Мы предлагаем широкий ассортимент продукции.
				У нас вы найдете все необходимое для обеспечения надежной работы трубопроводных систем.
				Обращайтесь к нам, ведь мы гарантируем быструю доставку товаров высокого качества и отличный сервис!</p>
			</div>
		</div>
	</div>
</div>

<!-- -->

<div class="section section3" id="Delivery ">
	<div class="block">
		<div class="block-item">
			<div class="block-image">
                <img alt="Image 1" src="/local/img/block/res1.png">
			</div>
			<div class="item-text">
				 Наши специалисты помогут <br>
				 с выбором продукции
			</div>
		</div>
		<div class="block-item">
			<div class="block-image">
                <img alt="Image 2" src="/local/img/block/res2.png">
			</div>
			<div class="item-text">
				 Выставим счет <br>
				 в течении 2-3 часов
			</div>
		</div>
		<div class="block-item">
			<div class="block-image">
                <img alt="Image 3" src="/local/img/block/res3.png">
			</div>
			<div class="item-text">
				 Осуществим доставку <br>
				 транспортной компанией <br>
				 по вашему выбору
			</div>
		</div>
	</div>
</div>

<!-- -->

<div class="section section4-title" id="Katalog">
	<div class="category-title">
		<h2>Каталог</h2>
	</div>
</div>

<div class="section section4" id="Katalog">
	<div class="category">
		<div class="category-table">
            <!-- Проверка и вывод списка разделов -->
            <?php if (!empty($arResult['SECTIONS'])): ?>
            <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
            <div class="category-block" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                <?php if ($arSection['PICTURE']): ?>
                    <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                    <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                <?php else: ?>
                    <img alt="Нет изображения" src="/local/img/no_image.png"> <!-- Замена на "заглушку", если изображения нет -->
                <?php endif; ?>
                <div class="category-text">
                    <?= $arSection['NAME']; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            Нет доступных категорий
            <?php endif; ?>
		</div>
	</div>
</div>

<!-- -->

<div class="section section5" id="Cash">
    <div class="form-container" id="Form">
        <form class="contact-form" id="contactForm" method="post" enctype="multipart/form-data">
            <h2>Оставить заявку</h2>

            <input type="text" id="name" name="name" placeholder="Ваше Имя" required>
            <input type="email" id="email" name="email" placeholder="e-mail" required>
            <input type="text" id="subject" name="subject" placeholder="Название Вашей компании" required>
            <textarea id="message" name="message" rows="5" placeholder="Комментарий"></textarea>

            <div class="form-actions">
                <!--
                <label class="file-label">
                    <img src="/local/img/block/file0-pn.png" alt="file icon">
                    <input type="file" id="files" name="files[]" accept=".pdf,.docx,.txt" multiple style="display:none;">
                </label>
                -->
                <button type="submit" id="submitButton">Отправить</button>
            </div>

            <!-- Контейнер для отображения добавленных файлов и кнопки удаления -->
            <div id="fileList"></div>
        </form>
    </div>
</div>

<script>
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

    document.getElementById('contactForm').addEventListener('submit', function (e) {
        e.preventDefault();  // Предотвращаем перезагрузку страницы

        var formData = new FormData(this);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'send.php', true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                // Очистка формы после успешной отправки
                document.getElementById('contactForm').reset();
                document.getElementById('fileList').innerHTML = '';  // Очищаем список прикрепленных файлов
                alert('Ваше сообщение было успешно отправлено!');
            } else {
                alert('Произошла ошибка при отправке сообщения.');
            }
        };

        xhr.send(formData);  // Отправляем данные формы
    });

    // Обновление списка файлов и вывод списка файлов
    document.getElementById('files').addEventListener('change', function () {
        var fileList = document.getElementById('fileList');
        fileList.innerHTML = '';  // Очищаем список перед обновлением

        // Выводим список файлов с возможностью удаления
        for (var i = 0; i < this.files.length; i++) {
            var file = this.files[i];
            var fileItem = document.createElement('div');
            fileItem.classList.add('file-item');

            var fileName = document.createElement('span');
            fileName.textContent = file.name;

            var deleteButton = document.createElement('button');
            deleteButton.classList.add('delete-file');
            deleteButton.textContent = 'Удалить';
            deleteButton.addEventListener('click', function () {
                fileItem.remove();  // Удаляем файл из списка
                // Логика для удаления файла из FormData не реализована в стандартном API,
                // если необходимо, придется перезаписывать FormData без удаленного файла.
            });

            fileItem.appendChild(fileName);
            fileItem.appendChild(deleteButton);
            fileList.appendChild(fileItem);
        }
    });

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
</script>

<!-- -->

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>
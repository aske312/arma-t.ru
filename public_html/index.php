<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->SetTitle("Главная");

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Iblock;

Loader::includeModule('iblock');

Asset::getInstance()->addCss("/resources/css/home.css"); // CSS
Asset::getInstance()->addJs("/resources/js/script.js"); // JS

// Получение данных для слайдера (ID = 1)
$sliderItems = [];
$res = CIBlockElement::GetList(
    ["SORT" => "ASC"],
    ["IBLOCK_ID" => 2, "ACTIVE" => "Y"],
    false,
    false,
    ["ID", "NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT"]
);
while ($arItem = $res->GetNext()) {
    $imgPath = CFile::GetPath($arItem["PREVIEW_PICTURE"]);
    $sliderItems[] = [
        "TEXT" => $arItem["PREVIEW_TEXT"],
        "IMG" => $imgPath,
    ];
}

// Получение данных для компании (ID = 2)
$companyInfo = "";
$res = CIBlockElement::GetList(
    ["SORT" => "ASC"],
    ["IBLOCK_ID" => 0, "ACTIVE" => "Y"],
    false,
    false,
    ["ID", "NAME", "DETAIL_TEXT", "PREVIEW_PICTURE"]
);
if ($arItem = $res->GetNext()) {
    $companyInfo = [
        "TEXT" => $arItem["DETAIL_TEXT"],
        "IMG" => CFile::GetPath($arItem["PREVIEW_PICTURE"]),
    ];
}

// Получение данных для доставки (ID = 3)
$deliveryItems = [];
$res = CIBlockElement::GetList(
    ["SORT" => "ASC"],
    ["IBLOCK_ID" => 0, "ACTIVE" => "Y"],
    false,
    false,
    ["ID", "NAME", "PREVIEW_PICTURE", "PREVIEW_TEXT"]
);
while ($arItem = $res->GetNext()) {
    $imgPath = CFile::GetPath($arItem["PREVIEW_PICTURE"]);
    $deliveryItems[] = [
        "TEXT" => $arItem["PREVIEW_TEXT"],
        "IMG" => $imgPath,
    ];
}

// Получение данных для каталога (ID = 5)
$sectionsFilter = [
    'IBLOCK_ID' => 1,
    'ACTIVE' => 'Y',
    'GLOBAL_ACTIVE' => 'Y',
];
$arSelect = ['ID', 'NAME', 'PICTURE'];
$sections = CIBlockSection::GetList(['SORT' => 'ASC'], $sectionsFilter, false, $arSelect);

$arResult['SECTIONS'] = [];
while ($section = $sections->Fetch()) {
    $arResult['SECTIONS'][] = $section;
}
?>

<div class="section section1">
    <div class="slider-wrapper">
        <div class="slider-container">
            <div class="slider">
                <div class="slides">
                    <?php foreach ($sliderItems as $slide): ?>
                    <div class="slide">
                        <img alt="Slide" src="<?= $slide['IMG'] ?>">
                        <div class="slide-text"><?= $slide['TEXT'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="dots">
                    <?php foreach ($sliderItems as $index => $slide): ?>
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
                <img alt="Image" src="/resources/img/block/312asd.png">
			</div>
			<div class="text">
				<p>АРМА-Т - специализируется на продаже запорной арматуры. Мы предлагаем широкий ассортимент продукции.
				У нас вы найдете все необходимое для обеспечения надежной работы трубопроводных систем.
				Обращайтесь к нам, ведь мы гарантируем быструю доставку товаров высокого качества и отличный сервис!</p>
			</div>
		</div>
	</div>
</div>

<div class="section section3" id="Delivery">
	<div class="block">
		<div class="block-item">
			<div class="block-image">
                <img alt="Image 1" src="/resources/img/block/res1.png">
			</div>
			<div class="item-text">
				 Наши специалисты помогут <br>
				 с выбором продукции
			</div>
		</div>
		<div class="block-item">
			<div class="block-image">
                <img alt="Image 2" src="/resources/img/block/res2.png">
			</div>
			<div class="item-text">
				 Выставим счет <br>
				 в течение 2-3 часов
			</div>
		</div>
		<div class="block-item">
			<div class="block-image">
                <img alt="Image 3" src="/resources/img/block/res3.png">
			</div>
			<div class="item-text">
				 Осуществим доставку <br>
				 транспортной компанией <br>
				 по вашему выбору
			</div>
		</div>
	</div>
</div>

<div class="section section4-title" id="catalog">
    <div class="category-title">
        <h2>Каталог</h2>
    </div>
</div>

<div class="section section4" id="catalog">
    <div class="category">
        <div class="category-table">
            <?php if (!empty($arResult['SECTIONS'])): ?>
                <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                    <div class="category-block" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                        <?php if ($arSection['PICTURE']): ?>
                            <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                            <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                        <?php else: ?>
                            <img alt="Нет изображения" src="/resources/img/no_image.png">
                        <?php endif; ?>
                        <div class="category-text"><?= $arSection['NAME']; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                Нет доступных категорий
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- -->

<div class="section section6-title" id="delivery-title">
    <div class="delivery-title">
        <h2>Доставка</h2>
    </div>
</div>

<div class="section section6" id="delivery">
    <div class="d-container">
        <div class="d-content">
            <div class="d-text">
                <div class="d-image">
                    <img alt="Image" src="/resources/img/block/312asd.png">
                </div>
                <div class="d-text-content">
                    <p> Доступен самовывоз продукции со склада.
                    В случае самовывоза потребуется печать или доверенность от организации.
                    Осуществим доставку транспортной компанией по вашему выбору.
                    При необходимости доставки вы можете уведомить об этом менеджера перед оформлением заказа
                    (сообщите адрес и способ доставки).  </p>
                </div>
            </div>
            <div class="d-list">
                <div class="d-icons">
                    <div class="d-icon">
                        <img src="/resources/img/companies/dellin.png" alt="Деловые Линии">
                    </div>
                    <div class="d-icon">
                        <img src="/resources/img/companies/pek.jpg" alt="ПЭК">
                    </div>
                    <div class="d-icon">
                        <img src="/resources/img/companies/baikal.jpg" alt="Байкал Сервис">
                    </div>
                    <div class="d-icon">
                        <img src="/resources/img/companies/sdek.png" alt="СДЭК">
                    </div>
                </div>
            </div>
            <div class="d-title-list">
                <p>Мы работаем с известными транспортными компаниями.</p>
            </div>
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
            <input type="text" id="subject" name="subject" placeholder="Название компании" required>
            <textarea id="message" name="message" rows="5" placeholder="Комментарий"></textarea>

            <!-- Блок для кнопки и чекбокса -->
            <div class="form-actions">
                <button type="submit" id="submitButton">Отправить</button>
                <label for="newsletter" class="newsletter-label">
                    Согласен
                    <input type="checkbox" id="newsletter" name="newsletter" required>
                </label>
            </div>
        </form>
    </div>
</div>

<!-- -->

<div class="section section7-title" id="pay-title">
    <div class="pay-title">
        <h2>Оплата</h2>
    </div>
</div>

<div class="section section7" id="pay">
	<div class="p-container">
	     <div class="p-content">
            <div class="p-text">
                <div class="p-image">
                    <img alt="Image" src="/resources/img/block/312asd.png">
                </div>
                <div class="p-text-content">
                    <p>Вы можете оплатить заказ по безналичному расчету, через любой банк РФ. Для выставления счета от
                    вас потребуется - карточка организации с банковскими реквизитами, телефон, адрес доставки,
                    а также электронный адрес. Деньги поступают на расчетный счет на следующий день после оплаты.
                    Статус поступления средств можно уточнить у нашего менеджера.</p>
                </div>
            </div>
        </div>
	</div>
</div>

<!-- -->

<!-- <script src="https://www.google.com/recaptcha/api.js" async defer></script> -->

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

    function redirectToSection(sectionId) {
        // Строим URL для страницы каталога, передавая параметр SECTION_ID
        var url = "/catalog/index.php?SECTION_ID=" + sectionId;

        // Перенаправляем пользователя на соответствующую страницу
        window.location.href = url;
    }

    document.getElementById('contactForm').addEventListener('submit', function (e) {
        e.preventDefault();  // Предотвращаем перезагрузку страницы

        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true; // Блокируем кнопку

        const formData = new FormData(this);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/resources/src/send.php', true);

        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    document.getElementById('contactForm').reset();
                    alert(response.message);
                    setTimeout(() => {
                        submitButton.disabled = false;
                    }, 1800); // 180000 мс = 3 минуты
                } else {
                    alert(response.message);
                    submitButton.disabled = false;
                }
            } else if (xhr.status === 429) {
                const response = JSON.parse(xhr.responseText);
                alert(response.message); // Показываем сообщение о времени ожидания
                submitButton.disabled = false;
            } else {
                alert('Произошла ошибка при отправке сообщения.');
                submitButton.disabled = false;
            }
        };

        xhr.onerror = function () {
            alert('Произошла ошибка при отправке сообщения.');
            submitButton.disabled = false;
        };

        xhr.send(formData);
    });

    let slideIndex = 1;
    let slides = document.getElementsByClassName("slide");
    let dots = document.getElementsByClassName("dot");

    function showSlides() {
        // Скрываем все слайды
        for (let i = 0; i < slides.length; i++) {
            slides[i].style.display = 'none';
        }
        // Убираем класс 'active' у всех точек
        for (let i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(' active', '');
        }

        // Переход к следующему слайду
        slideIndex++;
        if (slideIndex > slides.length) { slideIndex = 1; } // Переход на первый слайд, если достигнут конец

        // Показываем текущий слайд
        slides[slideIndex - 1].style.display = 'block';
        // Подсвечиваем текущую точку
        dots[slideIndex - 1].className += ' active';
    }

    // Функция для перехода к определенному слайду при клике на точку
    function currentSlide(n) {
        slideIndex = n;
        showSlides();
        resetAutoSlide(); // Сброс интервала при переходе вручную
    }

    // Функция для сброса и установки нового таймера
    function resetAutoSlide() {
        clearInterval(slideInterval); // Останавливаем предыдущий интервал
        slideInterval = setInterval(showSlides, 5000); // Устанавливаем новый интервал для автоматического переключения слайдов
    }

    // Автоматическое переключение слайдов
    slideInterval = setInterval(showSlides, 5000); // Переход каждые 5 секунд
</script>

<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>

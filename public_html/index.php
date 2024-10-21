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
if (CModule::IncludeModule('iblock')) {
    $res = CIBlockElement::GetList(
        ["SORT" => "ASC"],
        ["IBLOCK_ID" => 1, "ACTIVE" => "Y"],
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
}

// Получение данных для компании (ID = 2)
$companyInfo = "";
$res = CIBlockElement::GetList(
    ["SORT" => "ASC"],
    ["IBLOCK_ID" => 2, "ACTIVE" => "Y"],
    false,
    false,
    ["ID", "NAME", "DETAIL_TEXT"]
);
if ($arItem = $res->GetNext()) {
    $companyInfo = $arItem["DETAIL_TEXT"];
}

// Получение данных для доставки (ID = 3)
$deliveryItems = [];
$res = CIBlockElement::GetList(
    ["SORT" => "ASC"],
    ["IBLOCK_ID" => 3, "ACTIVE" => "Y"],
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
    'IBLOCK_ID' => 5,
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
                        <div class="slide-text">
                            <?= $slide['TEXT'] ?>
                        </div>
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

<div class="section section2" id="Company">
    <div class="container">
        <div class="content">
            <div class="text">
                <?= $companyInfo ?>
            </div>
        </div>
    </div>
</div>

<div class="section section3" id="Delivery">
    <div class="block">
        <?php foreach ($deliveryItems as $item): ?>
        <div class="block-item">
            <div class="block-image">
                <img alt="<?= $item['TEXT'] ?>" src="<?= $item['IMG'] ?>">
            </div>
            <div class="item-text">
                <?= $item['TEXT'] ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="section section4-title" id="Katalog">
    <div class="category-title">
        <h2>Каталог</h2>
    </div>
</div>

<div class="section section4" id="Katalog">
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

<div class="section section5" id="Cash">
    <div class="form-container" id="Form">
        <form class="contact-form" id="contactForm" method="post" enctype="multipart/form-data">
            <h2>Оставить заявку</h2>
            <input type="text" id="name" name="name" placeholder="Ваше Имя" required>
            <input type="email" id="email" name="email" placeholder="e-mail" required>
            <input type="text" id="subject" name="subject" placeholder="Название Вашей компании" required>
            <textarea id="message" name="message" rows="5" placeholder="Комментарий"></textarea>
            <div class="form-actions">
                <button type="submit" id="submitButton">Отправить</button>
            </div>
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
                document.getElementById('contactForm').reset();
                document.getElementById('fileList').innerHTML = '';  // Очищаем список прикрепленных файлов
                alert('Ваше сообщение было успешно отправлено!');
            } else {
                alert('Произошла ошибка при отправке сообщения.');
            }
        };

        xhr.send(formData);  // Отправляем данные формы
    });

    let slideIndex = 0; // Изначальный индекс слайда
    let slides = document.getElementsByClassName('slide'); // Получаем все слайды
    let dots = document.getElementsByClassName('dot'); // Получаем все точки

    function showSlides() {
        for (let i = 0; i < slides.length; i++) {
            slides[i].style.display = 'none';
        }
        for (let i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(' active', '');
        }

        slideIndex++;
        if (slideIndex > slides.length) { slideIndex = 1; }

        slides[slideIndex - 1].style.display = 'block';
        dots[slideIndex - 1].className += ' active';
    }

    function startAutoSlide() {
        slideInterval = setInterval(showSlides, 5000); // Интервал переключения - 5 секунд
    }

    function stopAutoSlide() {
        clearInterval(slideInterval);
    }

    function currentSlide(n) {
        stopAutoSlide();
        slideIndex = n;
        showSlides();
        startAutoSlide();
    }

    window.onload = function() {
        showSlides();
        startAutoSlide();
    };

    for (let i = 0; i < dots.length; i++) {
        dots[i].addEventListener('click', function() {
            currentSlide(i + 1);
        });
    }
</script>

<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
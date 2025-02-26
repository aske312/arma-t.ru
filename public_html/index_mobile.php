<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->SetTitle("Главная");

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Iblock;

Loader::includeModule('iblock');

Asset::getInstance()->addCss("/resources/css/home.css"); // CSS
// Asset::getInstance()->addJs("/resources/js/script.js"); // JS

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

<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(99863521, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/99863521" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<!-- -->
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

<script>
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

<?php
// require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>

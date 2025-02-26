<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->SetTitle("Главная");

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Iblock;

Loader::includeModule('iblock');
Asset::getInstance()->addCss("/resources/css/home.css"); // CSS

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
<div class="slider-m">
    <div class="slides-m">
        <?php foreach ($sliderItems as $index => $slide): ?>
        <div class="slide-m" data-index="<?= $index ?>">
            <img alt="Slide" src="<?= $slide['IMG'] ?>">
            <div class="slide-text-m"><?= $slide['TEXT'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Кнопки навигации -->
    <button class="prev-m" onclick="prevSlide()">❮</button>
    <button class="next-m" onclick="nextSlide()">❯</button>

    <!-- Точки навигации -->
    <div class="dots-m">
        <?php foreach ($sliderItems as $index => $slide): ?>
        <span class="dot-m" data-index="<?= $index ?>"></span>
        <?php endforeach; ?>
    </div>
</div>

<!-- -->

<div>
    <div class="image-m">
        <img alt="Image" src="/resources/img/block/312asd.png">
    </div>
    <div class="text-m">
        <p>АРМА-Т - специализируется на продаже запорной арматуры. Мы предлагаем широкий ассортимент продукции.
        У нас вы найдете все необходимое для обеспечения надежной работы трубопроводных систем.
        Обращайтесь к нам, ведь мы гарантируем быструю доставку товаров высокого качества и отличный сервис!</p>
    </div>
</div>

<!-- -->

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let slideIndex = 0;
        const slides = document.querySelectorAll(".slide-m");
        const dots = document.querySelectorAll(".dot-m");
        const slidesContainer = document.querySelector(".slides-m");
        let slideInterval;

        if (!slides.length || !dots.length || !slidesContainer) {
            console.error("Ошибка: слайдер не найден!");
            return;
        }

        function showSlide(index) {
            slideIndex = (index + slides.length) % slides.length;
            slidesContainer.style.transform = `translateX(-${slideIndex * 100}%)`;
            dots.forEach(dot => dot.classList.remove("active"));
            dots[slideIndex].classList.add("active");
        }

        window.nextSlide = function () {
            showSlide(slideIndex + 1);
            resetInterval();
        };

        window.prevSlide = function () {
            showSlide(slideIndex - 1);
            resetInterval();
        };

        window.currentSlide = function (index) {
            showSlide(index);
            resetInterval();
        };

        dots.forEach(dot => {
            dot.addEventListener("click", function () {
                currentSlide(parseInt(this.dataset.index));
            });
        });

        function startAutoSlide() {
            slideInterval = setInterval(nextSlide, 5000);
        }

        function resetInterval() {
            clearInterval(slideInterval);
            startAutoSlide();
        }

        showSlide(slideIndex);
        startAutoSlide();
    });

    function redirectToSection(sectionId) {
        var url = "/catalog/index.php?SECTION_ID=" + sectionId;
        window.location.href = url;
    }
</script>

<?php
// require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>

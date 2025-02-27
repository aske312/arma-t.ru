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

<div class="section-2-m">
    <div class="image-m">
        <img alt="Image" src="/resources/img/block/312asd.png">
    </div>
    <div class="text-m">
        <p>АРМА-Т специализируется на продаже запорной арматуры.
        У нас широкий ассортимент продукции для надежной работы трубопроводных систем.
        Гарантируем быструю доставку, высокое качество и отличный сервис!</p>
    </div>
</div>

<!-- -->

<div class="section3-m" id="Delivery">
    <div class="swiper-container-m">
        <div class="block-m">
            <div class="block-item-m">
                <div class="block-image-m">
                    <img alt="Image 1" src="/resources/img/block/res1.png">
                </div>
                <div class="item-text-m">
                     Наши специалисты помогут <br>
                     с выбором продукции
                </div>
            </div>
            <div class="block-item-m active"> <!-- Начальный центрированный элемент -->
                <div class="block-image-m">
                    <img alt="Image 2" src="/resources/img/block/res2.png">
                </div>
                <div class="item-text-m">
                     Выставим счет <br>
                     в течение 2-3 часов
                </div>
            </div>
            <div class="block-item-m">
                <div class="block-image-m">
                    <img alt="Image 3" src="/resources/img/block/res3.png">
                </div>
                <div class="item-text-m">
                     Осуществим доставку <br>
                     транспортной компанией <br>
                     по вашему выбору
                </div>
            </div>
        </div>
    </div>
</div>

<!-- -->

<div class="section4-title-m" id="catalog">
    <div class="category-title-m">
        <h2>Каталог</h2>
    </div>
</div>

<div class="section4-m" id="catalog-m">
    <div class="category-m">
        <div class="category-table-m">
            <?php if (!empty($arResult['SECTIONS'])): ?>
                <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                    <div class="category-block-m" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                        <?php if ($arSection['PICTURE']): ?>
                            <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                            <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                        <?php else: ?>
                            <img alt="Нет изображения" src="/resources/img/no_image.png">
                        <?php endif; ?>
                        <div class="category-text-m"><?= $arSection['NAME']; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                Нет доступных категорий
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- -->

<div class="section6-title-m" id="delivery-title-m">
    <div class="delivery-title-m">
        <h2>Доставка</h2>
    </div>
</div>

<div class="section6-m" id="delivery">
    <div class="d-container-m">
        <div class="d-content-m">
            <div class="d-text-m">
                <div class="d-image-m">
                    <img alt="Image" src="/resources/img/block/312asd.png">
                </div>
                <div class="d-text-content-m">
                    <p>Доступен самовывоз со склада (потребуется печать или доверенность).
                    Также возможна доставка транспортной компанией по вашему выбору.
                    Уведомьте менеджера об адресе и способе доставки перед оформлением заказа.</p>
                </div>
            </div>
            <div class="d-list-m">
                <div class="d-icons-m">
                    <div class="d-icon-m">
                        <img src="/resources/img/companies/dellin.png" alt="Деловые Линии">
                    </div>
                    <div class="d-icon-m">
                        <img src="/resources/img/companies/pek.jpg" alt="ПЭК">
                    </div>
                    <div class="d-icon-m">
                        <img src="/resources/img/companies/baikal.jpg" alt="Байкал Сервис">
                    </div>
                    <div class="d-icon-m">
                        <img src="/resources/img/companies/sdek.png" alt="СДЭК">
                    </div>
                </div>
            </div>
            <div class="d-title-list-m">
                <p>Мы работаем с известными транспортными компаниями.</p>
            </div>
        </div>
    </div>
</div>

<!-- -->

<div class="section5-m" id="Cash">
    <div class="form-container-m" id="Form">
        <form class="contact-form-m" id="contactForm" method="post" enctype="multipart/form-data">
            <h2>Оставить заявку</h2>
            <input type="text" id="name" name="name" placeholder="Ваше Имя" required>
            <input type="email" id="email" name="email" placeholder="e-mail" required>
            <input type="text" id="subject" name="subject" placeholder="Название компании" required>
            <textarea id="message" name="message" rows="5" placeholder="Комментарий"></textarea>

            <!-- Блок для кнопки и чекбокса -->
            <div class="form-actions-m">
                <button type="submit" id="submitButton">Отправить</button>
                <label for="newsletter" class="newsletter-label-m">
                    Согласие на рассылку <input type="checkbox" id="newsletter" name="newsletter" required>
                </label>
            </div>
        </form>
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

    document.addEventListener("DOMContentLoaded", function () {
        const slider = document.querySelector(".block-m");
        const slides = document.querySelectorAll(".block-item-m");
        let index = 1; // Начинаем с центрального блока
        let startX = 0;
        let moveX = 0;
        let isSwiping = false;

        function updateSlider() {
            slides.forEach((slide, i) => {
                slide.classList.remove("active");
                if (i === index) slide.classList.add("active");
            });
            const offset = -(index * 33.3) + 33.3; // Центрируем текущий элемент
            slider.style.transform = `translateX(${offset}%)`;
        }

        function nextSlide() {
            if (index < slides.length - 1) {
                index++;
                updateSlider();
            }
        }

        function prevSlide() {
            if (index > 0) {
                index--;
                updateSlider();
            }
        }

        slider.addEventListener("touchstart", (e) => {
            startX = e.touches[0].clientX;
            isSwiping = true;
        });

        slider.addEventListener("touchmove", (e) => {
            if (!isSwiping) return;
            moveX = e.touches[0].clientX - startX;
        });

        slider.addEventListener("touchend", () => {
            if (moveX > 50) prevSlide();
            if (moveX < -50) nextSlide();
            isSwiping = false;
            moveX = 0;
        });

        updateSlider();
    });

    function redirectToSection(sectionId) {
        var url = "/catalog/index.php?SECTION_ID=" + sectionId;
        window.location.href = url;
    }

</script>

<?php
// require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>

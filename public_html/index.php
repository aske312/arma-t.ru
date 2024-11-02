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

// Получение данных для компании (ID = 2)
$companyInfo = "";
$res = CIBlockElement::GetList(
    ["SORT" => "ASC"],
    ["IBLOCK_ID" => 2, "ACTIVE" => "Y"],
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

<!-- <div class="section section2" id="Company">
    <div class="container">
        <div class="content">
            <div class="text">
                <?php if ($companyInfo): ?>
                    <img src="<?= $companyInfo['IMG'] ?>" alt="Компания">
                    <div><?= $companyInfo['TEXT'] ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> -->

<!-- -->

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
				 в течении 2-3 часов
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

<!-- <div class="section section3" id="Delivery">
    <div class="block">
        <?php foreach ($deliveryItems as $item): ?>
        <div class="block-item">
            <div class="block-image">
                <img alt="<?= $item['TEXT'] ?>" src="<?= $item['IMG'] ?>">
            </div>
            <div class="item-text"><?= $item['TEXT'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div> -->

<!-- -->

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
	<div class="container">
		<div class="content">
			<div class="d-text">
				<p>Доступен самовывоз продукции со склада.
				В случае самовывоза потребуется печать или доверенность от организации.
				Осуществим доставку транспортной компанией по вашему выбору.
				При необходимости доставки Вы можете уведомить об этом менеджера перед оформлением заказа
				(сообщите адрес и способ доставки).</p>
			</div>
			<div class="d-list">
                <div class="image">
                    <img alt="Image" src="/resources/img/block/312asd.png">
			    </div>
				<p>Мы работаем со следующими транспортными компаниями: </p>
				<ol>
				    <li>Деловые Линии</li>
				    <li>ПЭК</li>
				    <li>Байкал Сервис</li>
				    <li>СДЭК (до 35кг.) и др. Бесплатная доставка до ближайшего терминала ТК.</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<!-- -->

<div class="section section7-title" id="сontact-title">
    <div class="сontact-title">
        <h2>Оплата</h2>
    </div>
</div>

<div class="section section7" id="сontacts">
	<div class="container">
		<div class="content">
			<div class="text">
				<p>Вы можете оплатить Ваш заказ по безналичному расчету, через любой банк РФ. Для выставления счета от
				Вас потребуется - карточка организации с банковскими реквизитами, телефон, адрес доставки,
				а также электронный адрес. Деньги поступают на расчетный счет на следующий день после оплаты.
				Статус поступления средств Вы можете уточнить у нашего менеджера.</p>
			</div>
			<div class="d-list">
			    <p>Наши реквизиты:</p>
                <div class="image">
                    <img alt="Image" src="/resources/img/block/312asd.png">
			    </div>

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

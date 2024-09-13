<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->SetTitle("Главная");

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;
use Bitrix\Iblock;

Loader::includeModule('iblock');

//Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/css/main.css");
Asset::getInstance()->addCss("/local/css/header.css");  //css
Asset::getInstance()->addCss("/local/css/home.css"); //css
Asset::getInstance()->addCss("/local/css/footer.css");  //css
Asset::getInstance()->addJs("/local/js/script.js");     // js

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
    $arFilter = ["IBLOCK_ID" => $IBLOCK_ID, "ACTIVE" => "Y"]; // Укажите ID инфоблока

    $res = CIBlockElement::GetList(["SORT" => "ASC"], $arFilter, false, false, $arSelect);
    while ($arItem = $res->GetNext()) {
        $imgPath = CFile::GetPath($arItem["PREVIEW_PICTURE"]);
        $slides[] = [
            "TEXT" => $arItem["PREVIEW_TEXT"],
            "IMG" => $imgPath,
        ];
    }
}?>

<!--
<div class="section section1">
	<div class="slider-wrapper">
		<div class="slider-container">
			<div class="slider">
				<div class="slides">
					<div class="slide">
                        <img alt="Slide 1" src="/local/img/head_title/steel-slide_10.jpg">
						<div class="slide-text">
							 АРМА-Т - надежный поставщик для вашего предприятия
						</div>
					</div>
					<div class="slide">
                        <img alt="Slide 2" src="/local/img/head_title/steel-slide_8.jpg">
						<div class="slide-text">

						</div>
					</div>
					<div class="slide">
                        <img alt="Slide 3" src="/local/img/head_title/steel-slide_9.jpg">
						<div class="slide-text">

						</div>
					</div>
				</div>
				<div class="dots">
                    <span class="dot"></span> <span class="dot"></span> <span class="dot"></span>
				</div>
			</div>
		</div>
	</div>
</div> -->

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
                    <span class="dot" onclick="currentSlide(<?= $index+1 ?>)"></span>
                    <?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
console.log(slides);  // Проверяем, загружаются ли слайды
console.log(dots);    // Проверяем, загружаются ли точки
</script>

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

<div class="section section3" id="Dostavka">
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
		<form class="contact-form" action="#" method="post">
			<h2>Оставить заявку</h2>
            <input type="text" id="name" name="name" placeholder="Ваше Имя" required=""> <input type="email" id="email" name="email" placeholder="e-mail" required=""> <input type="text" id="subject" name="subject" placeholder="Название Вашей компании" required=""> <textarea id="message" name="message" rows="5" placeholder="Комментарий" required=""></textarea> <button type="submit">Отправить</button>
		</form>
	</div>
</div>

<!-- -->

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>
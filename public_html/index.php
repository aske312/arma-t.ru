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
    $arFilter = ["IBLOCK_ID" => 2, "ACTIVE" => "Y"];

    $res = CIBlockElement::GetList(["SORT" => "ASC"], $arFilter, false, false, $arSelect);
    while ($arItem = $res->GetNext()) {
        $imgPath = CFile::GetPath($arItem["PREVIEW_PICTURE"]);
        $slides[] = [
            "TEXT" => $arItem["PREVIEW_TEXT"],
            "IMG" => $imgPath,
        ];
    }
}?>

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
        <form class="contact-form" id="contactForm">
            <h2>Оставить заявку</h2>
            <input type="text" id="name" name="name" placeholder="Ваше Имя" required>
            <input type="email" id="email" name="email" placeholder="e-mail" required>
            <input type="text" id="subject" name="subject" placeholder="Название Вашей компании" required>
            <textarea id="message" name="message" rows="5" placeholder="Комментарий" required></textarea>
            <button type="submit">Отправить</button>
        </form>
        <div id="formMessage" style="display:none; color:green;">Ваше сообщение отправлено!</div>
        <div id="formErrorMessage" style="display:none; color:red;">Произошла ошибка, попробуйте еще раз.</div>
    </div>
</div>

<script>
    document.getElementById('contactForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Получение данных формы
        var formData = new FormData(this);

        // AJAX-запрос для отправки данных на сервер
        fetch('send.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('formMessage').style.display = 'block';
                document.getElementById('formErrorMessage').style.display = 'none';
                this.reset();  // Сбрасываем форму
            } else {
                throw new Error(data.error);
            }
        })
        .catch(error => {
            document.getElementById('formErrorMessage').style.display = 'block';
            document.getElementById('formMessage').style.display = 'none';
        });
    });
</script>

<!-- -->

<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');?>
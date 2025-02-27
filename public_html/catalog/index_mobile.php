<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Каталог");

// Получаем параметр SECTION_ID из GET-запроса
$sectionId = isset($_GET['SECTION_ID']) ? $_GET['SECTION_ID'] : 'all';

// Подключаем CSS
use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/catalog.css");

// Фильтр для текущей секции
$sectionFilter = [
    'IBLOCK_ID' => 1,
    'ACTIVE' => 'Y',
];

if ($sectionId !== 'all') {
    $sectionFilter['ID'] = intval($sectionId);
}

// Получаем выбранную секцию, если она не 'all'
$selectedSection = ($sectionId !== 'all')
    ? CIBlockSection::GetList([], $sectionFilter, false, ['ID', 'NAME', 'DESCRIPTION'])->Fetch()
    : null;

// Получаем список всех активных секций для бокового меню
$sectionsFilter = [
    'IBLOCK_ID' => 1,
    'ACTIVE' => 'Y',
    'GLOBAL_ACTIVE' => 'Y',
];

$arSelect = ['ID', 'NAME', 'SECTION_PAGE_URL', 'PICTURE'];
$sections = CIBlockSection::GetList(['SORT' => 'ASC'], $sectionsFilter, false, $arSelect);
$arResult['SECTIONS'] = [];
while ($section = $sections->Fetch()) {
    $arResult['SECTIONS'][] = $section;
}

// Форма поиска
$searchQuery = isset($_GET['search']) ? urldecode(trim($_GET['search'])) : '';

// Фильтры
$filterValues = [
    'EL_CONNECTION_TYPE' => [],
    'EL_DRIVE_TYPE' => [],
    'EL_DN_DIAMETER_MM' => [],
    'EL_PN_PRESSURE_KGF_CM2' => [],
    'EL_FIGURE_TABLE' => [],
    'EL_BODY_MATERIAL' => []
];

// Фильтр для элементов каталога
$elementFilter = [
    'IBLOCK_ID' => 1,
    'ACTIVE' => 'Y',
    'INCLUDE_SUBSECTIONS' => 'Y', // Включаем подкатегории, если они есть
];

// Если SECTION_ID не 'all', добавляем фильтрацию по секции
if ($sectionId !== 'all') {
    $elementFilter['SECTION_ID'] = intval($sectionId);
}

// Если есть запрос в поиске, добавляем его в фильтр
if ($searchQuery) {
    $elementFilter['%NAME'] = $searchQuery;
}

// Применение фильтров из GET-параметров
$filterProperties = ['EL_CONNECTION_TYPE', 'EL_DRIVE_TYPE', 'EL_DN_DIAMETER_MM', 'EL_PN_PRESSURE_KGF_CM2', 'EL_BODY_MATERIAL', 'EL_FIGURE_TABLE'];
foreach ($filterProperties as $propertyCode) {
    if (isset($_GET[$propertyCode]) && $_GET[$propertyCode] !== 'all') {
        $elementFilter['PROPERTY_' . $propertyCode] = $_GET[$propertyCode];
    }
}

// Получаем все элементы в текущей секции или во всех секциях, если SECTION_ID='all'
$filterElementSelect = ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'PROPERTY_*'];
$res = CIBlockElement::GetList([], $elementFilter, false, false, $filterElementSelect);

// Перебираем все элементы для фильтров
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();

    // Собираем все уникальные значения для каждого фильтра
    foreach ($filterProperties as $propertyCode) {
        if (isset($arProps[$propertyCode]) && !empty($arProps[$propertyCode]['VALUE'])) {
            $values = is_array($arProps[$propertyCode]['VALUE']) ? $arProps[$propertyCode]['VALUE'] : [$arProps[$propertyCode]['VALUE']];
            foreach ($values as $value) {
                if (!in_array($value, $filterValues[$propertyCode])) {
                    $filterValues[$propertyCode][] = $value;
                }
            }
        }
    }
}

// Убираем дубли и сортируем значения для каждого фильтра
foreach ($filterValues as $propertyCode => $values) {
    $filterValues[$propertyCode] = array_unique($values);
    sort($filterValues[$propertyCode]);
}

// Получаем элементы с учётом фильтров и пагинации
$res = CIBlockElement::GetList(
    ['ID' => 'ASC'],
    $elementFilter,
    false,
    ['nPageSize' => 10],
    $elementSelect
);

$res->NavStart(10);
$arResult['NAV_STRING'] = $res->GetPageNavStringEx($navComponentObject, "", ".default");
?>

<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(99863669, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/99863669" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<!-- Названия каталога и описания -->
<div class="section-title-m">
    <h2><?= isset($selectedSection['NAME']) ? $selectedSection['NAME'] : 'Применен фильтр'; ?></h2>
    <p><?= isset($selectedSection['DESCRIPTION']) && !empty($selectedSection['DESCRIPTION']) ? $selectedSection['DESCRIPTION'] : 'Выберете необходимые позиции'; ?></p>
</div>

<div class="catalog-container-m">

    <div class="catalog-content-m">

        <!-- Заголовок для фильтров (кнопка) -->
        <button class="filter-title-m" onclick="toggleFilters()">
            Фильтры  <span id="filters-arrow">▼</span>
        </button>

        <!-- Фильтры --> <!--
        <div class="catalog-filters-m">
            <?php
            // Массив с соответствиями названий и единиц измерения
            $filterLabels = [
                'EL_DN_DIAMETER_MM' => ['label' => 'Диаметр DN', 'unit' => 'мм'],
                'EL_PN_PRESSURE_KGF_CM2' => ['label' => 'Давление PN', 'unit' => 'кгс/см²'],
                'EL_CONNECTION_TYPE' => ['label' => 'Тип присоединения'],
                'EL_DRIVE_TYPE' => ['label' => 'Тип привода'],
                'EL_BODY_MATERIAL' => ['label' => 'Материал корпуса'],
                'EL_FIGURE_TABLE' => ['label' => 'Таблица фигур']
            ];

            foreach ($filterValues as $propertyCode => $values):
                if (empty($values) || !isset($filterLabels[$propertyCode])) continue; // Пропуск, если нет значений или не в списке
            ?>
                <div class="filter-m">
                    <div class="filter-item-m">
                        <label for="<?= $propertyCode ?>" class="filter-label-m"><?= $filterLabels[$propertyCode]['label'] ?>:</label>
                        <div class="filter-content-m">
                            <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                <?php foreach ($values as $value): ?>
                                    <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                        <?= $value ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($filterLabels[$propertyCode]['unit'])): ?>
                                <span class="unit-m"><?= $filterLabels[$propertyCode]['unit'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div> -->

        <!-- Кнопка для открытия разделов -->
        <button class="category-button-m" onclick="toggleCategoryModal()">Разделы</button>

        <!-- Модальное окно с категориями -->
        <div id="category-modal" class="category-modal-m">
            <div class="category-modal-content-m">
                <span class="close-modal-m" onclick="toggleCategoryModal()">&times;</span>
                <h2>Выберите раздел</h2>

                <ul class="category-list-m">
                    <?php if (!empty($arResult['SECTIONS'])): ?>
                        <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                            <?php $isActive = ($arSection['ID'] == $sectionId) ? 'active' : ''; ?>
                            <li class="category-item-m <?= $isActive; ?>" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                                <?php if ($arSection['PICTURE']): ?>
                                    <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                                    <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                                <?php else: ?>
                                    <img alt="Нет изображения" src="/resources/img/no_image.png">
                                <?php endif; ?>
                                <span><?= $arSection['NAME']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Список элементов каталога --> <!--
        <div class="catalog-items-m">
            <?php
            $itemsFound = false;
            while ($ob = $res->GetNextElement()):
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();
                foreach ($arResult['SECTIONS'] as $arSection):
                    if ($arSection['ID'] == $arFields['IBLOCK_SECTION_ID']):
                        $itemsFound = true;
                        $productImage = '';
                        if (!empty($arProps['EL_IMAGES']['VALUE'])) {
                            $productImage = "/resources/img/production/" . $arProps['EL_IMAGES']['VALUE'];
                        } elseif ($arFields['PREVIEW_PICTURE']) {
                            $productImage = CFile::GetPath($arFields['PREVIEW_PICTURE']);
                        } elseif ($arFields['PICTURE']) {
                            $productImage = CFile::GetPath($arFields['PICTURE']);
                        } elseif ($arSection['PICTURE']) {
                            $productImage = CFile::GetPath($arSection['PICTURE']);
                        } else {
                            $productImage = "/resources/img/no_image.png";
                        }
            ?>

                <div class="catalog-item-m" data-id="<?= $arFields['ID']; ?>">
                    <a href="detail.php?ID=<?= $arFields['ID']; ?>" class="catalog-item-link-m">
                        <div class="catalog-item-header-m">
                            <img src="<?= $productImage; ?>" alt="<?= $arFields['NAME']; ?>" class="catalog-item-image-m">
                            <div class="catalog-item-info-m">
                                <h3 class="catalog-item-name-m"><?= $arFields['PREVIEW_TEXT']; ?></h3>
                                <p>Артикул: <?= $arProps['EL_ARTICLE']['VALUE']; ?></p>
                                <p><?= $arProps['EL_PRODUCTION_TIME']['VALUE']; ?></p>
                                <p>
                                    <div class="catalog-item-price-m">
                                        <?php $price = $arProps['EL_PURCHASE_PRICE']['VALUE'];
                                        if ($price == 0 || empty($price)) {
                                            echo 'По запросу';
                                        } else {
                                            echo $price . ' руб.';
                                        } ?>
                                    </div>
                                </p>
                            </div>

                            <div class="catalog-item-controls-m">
                                <button class="catalog-item-add-to-cart-m"
                                        data-id="<?= $arFields['ID']; ?>"
                                        data-image="<?= $productImage; ?>"
                                        data-name="<?= $arFields['PREVIEW_TEXT']; ?>"
                                        data-price="<?= $arProps['EL_PURCHASE_PRICE']['VALUE']; ?>"
                                        data-article="<?= $arProps['EL_ARTICLE']['VALUE']; ?>">
                                    В корзину
                                </button>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php endwhile; ?>
                <?php if (!$itemsFound): ?>
                <p>Ничего не найдено</p>
            <?php endif; ?>
        </div>
-->
        <!-- Пагинация --> <!--
        <div class="pagination-m">
            <?= $arResult['NAV_STRING']; ?>
        </div> -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>
<script>
function toggleCategoryModal() {
    const modal = document.getElementById("category-modal");
    modal.style.display = modal.style.display === "block" ? "none" : "block";
}

function redirectToSection(sectionId) {
    window.location.href = "?SECTION_ID=" + sectionId;
}
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
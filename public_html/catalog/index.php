<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Подключение модулей и стилей
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

Loader::includeModule('iblock');

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/catalog.css");
Asset::getInstance()->addCss("/local/css/header.css");
Asset::getInstance()->addCss("/local/css/footer.css");
Asset::getInstance()->addJs("/local/js/script.js");

// Получение списка секций для бокового меню
$sectionsFilter = [
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'ACTIVE' => 'Y',
    'GLOBAL_ACTIVE' => 'Y',
];
$arSelect = ['ID', 'NAME', 'SECTION_PAGE_URL', 'PICTURE'];
$sections = CIBlockSection::GetList(['SORT' => 'ASC'], $sectionsFilter, false, $arSelect);
$arResult['SECTIONS'] = [];
while ($section = $sections->Fetch()) {
    $arResult['SECTIONS'][] = $section;
}

// Инициализация свойств фильтров
$filterProperties = [
    'EL_TABLE_FIGURE',
    'EL_CONNECTION_TYPE',
    'EL_PRESSURE_PN',
    'EL_BODY_MATERIAL',
    'EL_TYPE_ZATVOR'
];

$arResult['FILTER_PROPERTIES'] = [];
foreach ($filterProperties as $propertyCode) {
    $arResult['FILTER_PROPERTIES'][$propertyCode] = [
        'NAME' => CIBlockProperty::GetByID($propertyCode, $arParams['IBLOCK_ID'])->Fetch()['NAME'],
        'VALUES' => []
    ];
}

// Получение всех секций и сбор значений для фильтров
$sections = CIBlockSection::GetList(['SORT' => 'ASC'], $sectionsFilter, false, ['ID']);
while ($section = $sections->Fetch()) {
    $elementFilter = [
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'SECTION_ID' => $section['ID'],
        'ACTIVE' => 'Y',
        'INCLUDE_SUBSECTIONS' => 'Y',
    ];

    $elementSelect = ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PROPERTY_*'];
    $res = CIBlockElement::GetList([], $elementFilter, false, false, $elementSelect);

    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();

        foreach ($filterProperties as $propertyCode) {
            $value = $arProps[$propertyCode]['VALUE'];

            if (!empty($value)) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $arResult['FILTER_PROPERTIES'][$propertyCode]['VALUES'][] = $val;
                    }
                } else {
                    $arResult['FILTER_PROPERTIES'][$propertyCode]['VALUES'][] = $value;
                }
            }
        }
    }
}

// Удаление дублирующихся значений в фильтрах
foreach ($arResult['FILTER_PROPERTIES'] as &$property) {
    if (!empty($property['VALUES'])) {
        $property['VALUES'] = array_unique($property['VALUES']);
    }
}

// Применение фильтров к элементам каталога
$elementFilter = [
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'ACTIVE' => 'Y',
    'INCLUDE_SUBSECTIONS' => 'Y',
];

foreach ($filterProperties as $propertyCode) {
    if (isset($_GET[$propertyCode]) && $_GET[$propertyCode] !== 'all') {
        $elementFilter['PROPERTY_' . $propertyCode] = $_GET[$propertyCode];
    }
}

// Получение списка элементов с учетом фильтров и пагинации
$elementSelect = ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PROPERTY_*'];
$res = CIBlockElement::GetList(
    [$arParams['ELEMENT_SORT_FIELD'] => $arParams['ELEMENT_SORT_ORDER']],
    $elementFilter,
    false,
    ['nPageSize' => 10],
    $elementSelect
);

$res->NavStart(10);
$arResult['NAV_STRING'] = $res->GetPageNavStringEx($navComponentObject, "", ".default");
?>

        <!-- BODY -->
        <div class="section-title">
            <h2>Список каталога</h2>
            <p>Выберете раздел, или фильтр</p>
        </div>

        <div class="catalog-container">

            <div class="catalog-sidebar">
                <ul id="catalog-menu" class="catalog-menu">
                    <?php if (!empty($arResult['SECTIONS'])): ?>
                    <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                    <li>
                        <div class="category-block" onclick="redirectToSection(<?= $arSection['ID'] ?>)">
                            <a href="<?= $arSection['SECTION_PAGE_URL'] ?>">
                                <img src="<?= CFile::GetPath($arSection['PICTURE']) ?>" alt="<?= $arSection['NAME'] ?>">
                                <span><?= $arSection['NAME'] ?></span>
                            </a>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="catalog-content">

                <div class="filters">
                    <h3>Фильтры</h3>
                    <?php foreach ($arResult['FILTER_PROPERTIES'] as $property): ?>
                    <div class="filter-group">
                        <h4><?= $property['NAME'] ?></h4>
                        <select onchange="applyFilter(this)">
                            <option value="all">Все</option>
                            <?php foreach ($property['VALUES'] as $value): ?>
                            <option value="<?= $value ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="select-all">
                    <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
                    <label for="select-all">Выбрать все</label>
                    <button class="catalog-add-all" onclick="addAllToCart()">В корзину</button>
                </div>

                <div class="catalog-items">
                    <?php
                        while ($ob = $res->GetNextElement()):
                        $arFields = $ob->GetFields();
                        $arProps = $ob->GetProperties();
                        foreach ($arResult['SECTIONS'] as $arSection):
                        if ($arSection['ID'] == $arFields['IBLOCK_SECTION_ID']):
                    ?>
                    <div class="catalog-item">
                        <div class="catalog-item-header">
                            <input type="checkbox" class="catalog-item-checkbox" id="item-<?= $arFields['ID']; ?>">
                            <?php if ($arSection['PICTURE']): ?>
                            <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                            <img src="<?= $imgPath; ?>" alt="<?= $arSection['NAME']; ?>" class="catalog-item-image">
                            <?php else: ?>
                            <img alt="Нет изображения" src="/local/img/no_image.png">
                            <?php endif; ?>
                            <div class="catalog-item-info">
                                <h3 class="catalog-item-name"><?= $arFields['NAME']; ?></h3>
                                <p>Артикул: <?= $arProps['EL_ARTICUL']['VALUE']; ?></p>
                                <p><div class="catalog-item-price"><?= $arProps['EL_PRICE']['VALUE']; ?> руб.</div></p>
                            </div>
                            <div class="catalog-item-controls">
                                <button class="catalog-item-add-to-cart" onclick="event.stopPropagation(); addToCart(<?= $arFields['ID']; ?>, '<?= $arProps['EL_ARTICUL']['VALUE']; ?>', <?= $arProps['EL_PRICE']['VALUE']; ?>);">
                                    В корзину
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endwhile; ?>
                </div>

                <div class="pagination">
                    <?= $arResult['NAV_STRING']; ?>
                </div>
            </div>
        </div>

        <script>
            function addAllToCart() {
                const checkboxes = document.querySelectorAll('.catalog-item-checkbox:checked');
                checkboxes.forEach(checkbox => {
                    const itemId = checkbox.id.split('-')[1];
                    const article = document.querySelector(`#item-${itemId} .catalog-item-name`).innerText;
                    const price = parseFloat(document.querySelector(`#item-${itemId} .catalog-item-price`).innerText);
                    addToCart(itemId, article, price);
                });
            }

            function toggleSelectAll(selectAllCheckbox) {
                const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            }

            function applyFilter(select) {
                const property = select.previousElementSibling.innerText;
                const value = select.value;
                const url = new URL(window.location.href);
                if (value === 'all') {
                    url.searchParams.delete(property);
                } else {
                    url.searchParams.set(property, value);
                }
                window.location.href = url.toString();
            }
        </script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

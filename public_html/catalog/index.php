<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Каталог");

//session_start()

// Берем значение секции
$sectionId = intval($_GET['SECTION_ID']);
if (empty($sectionId)) {
    $sectionId = 1;
}

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss("/resources/css/catalog.css"); //css

// Получение текущей секции
$sectionFilter = [
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'ID' => $sectionId,
    'ACTIVE' => 'Y',
];
$selectedSection = CIBlockSection::GetList([], $sectionFilter, false, ['ID', 'NAME', 'DESCRIPTION'])->Fetch();

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
        'SECTION_ID' => $sectionId,
        'ACTIVE' => 'Y',
        'INCLUDE_SUBSECTIONS' => 'Y',
    ];

    $elementSelect = ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PROPERTY_*'];
    $res = CIBlockElement::GetList([], $elementFilter, false, false, $elementSelect);

    // Проход по каждому элементу
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

//// Наполнение фильтра
//while ($ob = $res->GetNextElement()) {
//    $arFields = $ob->GetFields();
//    $arProps = $ob->GetProperties();
//
//    foreach ($filterProperties as $propertyCode) {
//        $value = $arProps[$propertyCode]['VALUE'];
//
//        if (!empty($value)) {
//            if (is_array($value)) {
//                foreach ($value as $val) {
//                    $arResult['FILTER_PROPERTIES'][$propertyCode]['VALUES'][] = $val;
//                }
//            } else {
//                $arResult['FILTER_PROPERTIES'][$propertyCode]['VALUES'][] = $value;
//            }
//        }
//    }
//}

// Удаление дублирующихся значений в фильтрах
foreach ($arResult['FILTER_PROPERTIES'] as &$property) {
    if (!empty($property['VALUES'])) {
        $property['VALUES'] = array_unique($property['VALUES']);
    }
}

// Применение фильтров к элементам каталога
$elementFilter = [
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    'SECTION_ID' => $sectionId,
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
    ['ID' => 'ASC'], // Сортировка по ID
    $elementFilter,
    false,
    ['nPageSize' => 10],  // Пагинация: по 10 элементов на страницу
    $elementSelect
);

$res->NavStart(10); // Устанавливаем навигацию
$arResult['NAV_STRING'] = $res->GetPageNavStringEx($navComponentObject, "", ".default");
?>

<!-- Названия каталога и описания -->
<div class="section-title">
    <h2><?= isset($selectedSection['NAME']) ? $selectedSection['NAME'] : 'Применен фильтр'; ?></h2>
    <p><?= isset($selectedSection['DESCRIPTION']) && !empty($selectedSection['DESCRIPTION']) ? $selectedSection['DESCRIPTION'] : 'Выберете необходимые позиции'; ?></p>
</div>

<div class="catalog-container">

    <!-- Боковое меню категорий -->
    <div class="catalog-sidebar">
        <ul id="catalog-menu" class="catalog-menu">
            <?php $sectionId = intval($_GET['SECTION_ID']); // Получаем ID активной секции из запроса
            if (!empty($arResult['SECTIONS'])): ?>
            <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
            <?php $isActive = ($arSection['ID'] == $sectionId) ? 'active' : ''; ?>
            <li>
                <div class="category-block <?= $isActive; ?>" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                    <?php if ($arSection['PICTURE']): ?>
                    <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                    <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                    <?php else: ?><img alt="Нет изображения" src="/resources/img/no_image.png"><?php endif; ?>
                    <div class="category-text"> <?= $arSection['NAME']; ?> </div>
                </div>
            </li>
            <?php endforeach; ?><?php endif; ?>
        </ul>
    </div>

    <div class="catalog-content">

        <!-- Анимация загрузки -->
        <div id="loader" class="loader" style="display: none;">Загрузка...</div>


        <!-- Фильтры
        <div class="filters">
            <form method="GET" action="">
                <table>
                    <?php if (!empty($arResult['FILTER_PROPERTIES'])): ?>
                    <?php foreach ($arResult['FILTER_PROPERTIES'] as $propertyCode => $property): ?>
                        <th>
                            <?= $property['NAME']; ?>
                            <select name="<?= $propertyCode; ?>">
                                <option value="all">Все</option>
                                <?php foreach ($property['VALUES'] as $value): ?>
                                <option value="<?= htmlspecialchars($value); ?>" <?= isset($_GET[$propertyCode]) && $_GET[$propertyCode] == $value ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($value); ?>
                                </option>
                            <?php endforeach; ?>
                            </select>
                        </th>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </table>
                <button type="submit">Применить фильтр</button>
            </form>
        </div> -->

        <!-- Чекбокс для выбора всех товаров -->
        <div class="select-all">
            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
            <label for="select-all">Выбрать все</label>
            <button class="catalog-add-all">В корзину</button>
        </div>

        <!-- Список элементов каталога  -->
        <div class="catalog-items">
            <?php while ($ob = $res->GetNextElement()):
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();
                ?>
                <div class="catalog-item" data-id="<?= $arFields['ID']; ?>">
                    <a href="detail.php?ELEMENT_ID=<?= $arFields['ID']; ?>" class="catalog-item-link">
                        <?php if ($arFields['PREVIEW_PICTURE']): ?>
                            <?php $imgPath = CFile::GetPath($arFields['PREVIEW_PICTURE']); ?>
                            <img src="<?= $imgPath; ?>" alt="<?= $arFields['NAME']; ?>" class="catalog-item-image">
                        <?php else: ?>
                            <img alt="Нет изображения" src="/resources/img/no_image.png" class="catalog-item-image">
                        <?php endif; ?>
                        <div class="catalog-item-info">
                            <h3 class="catalog-item-name"><?= $arFields['NAME']; ?></h3>
                            <p>Артикул: <?= $arProps['EL_ARTICLE_CODE']['VALUE']; ?></p>
                            <p class="catalog-item-price"><?= $arProps['EL_PURCHASE_PRICE']['VALUE'] ?: 'Цену уточняйте у оператора'; ?></p>
                        </div>
                    </a>

                    <div class="catalog-item-controls">
                        <button class="catalog-item-add-to-cart" data-id="<?= $arFields['ID']; ?>"
                                data-name="<?= $arFields['NAME']; ?>" data-price="<?= $arProps['EL_PRICE']['VALUE']; ?>"
                                data-article="<?= $arProps['EL_ARTICLE_CODE']['VALUE']; ?>">В корзину</button>
                    </div>
                </div>

                <!-- Краткое описание элемента -->
                <div class="catalog-item-properties">
                    <table>
                        <th>Тип присоединения: <?= $arProps['EL_CONNTYPE']['VALUE']; ?></th>
                        <th>Тип привода: <?= $arProps['EL_DRIVETYPE']['VALUE']; ?></th>
                        <th>Диаметр DN: <?= $arProps['EL_DN_DIAMETER_MM']['VALUE']; ?>мм</th>
                        <th>Давление PN: <?= $arProps['EL_PN_PRESSURE_KGF_CM2']['VALUE']; ?>кгс/см²</th>
                        <th>Материал корпуса: <?= $arProps['EL_BODY_MATERIAL']['VALUE']; ?></th>
                    </table>
                </div>
            </div>
            <?php endif; ?><?php endforeach; ?><?php endwhile; ?>
        </div>

        <!-- Пагинация  -->
        <div class="pagination">
            <?= $arResult['NAV_STRING']; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>
<script>
    // Показываем анимацию загрузки
    function showLoader(show) {
        document.getElementById('loader').style.display = show ? 'block' : 'none';
    }

    // Добавление товара в корзину
    function addToCart(productId, productName, productPrice, productArticle) {
        let cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        const itemIndex = cartItems.findIndex(item => item.id === productId);

        if (itemIndex !== -1) {
            cartItems[itemIndex].quantity += 1;
        } else {
            cartItems.push({ id: productId, name: productName, price: productPrice, article: productArticle, quantity: 1 });
        }

        localStorage.setItem('cartItems', JSON.stringify(cartItems));
        updateCartCounter();
    }

    // Добавление всех выбранных товаров в корзину
    document.querySelector('.catalog-add-all').addEventListener('click', () => {
        document.querySelectorAll('.catalog-item-checkbox:checked').forEach(itemCheckbox => {
            const itemId = itemCheckbox.dataset.id;
            const itemName = itemCheckbox.dataset.name;
            const itemPrice = itemCheckbox.dataset.price;
            const itemArticle = itemCheckbox.dataset.article;
            addToCart(itemId, itemName, itemPrice, itemArticle);
        });
        alert('Товары добавлены в корзину');
    });

    // Показ количества товаров в корзине
    function updateCartCounter() {
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        const itemCount = cartItems.reduce((total, item) => total + item.quantity, 0);
        document.getElementById('cart-count').textContent = itemCount;
    }

    // Инициализация обработчиков событий для кнопок "В корзину"
    document.querySelectorAll('.catalog-item-add-to-cart').forEach(button => {
        button.addEventListener('click', event => {
            const itemId = itemCheckbox.dataset.id;
            const itemName = itemCheckbox.dataset.name;
            const itemPrice = itemCheckbox.dataset.price;
            const itemArticle = itemCheckbox.dataset.article;
            addToCart(itemId, itemName, itemPrice, itemArticle);
        });
    });

    document.addEventListener("DOMContentLoaded", updateCartCounter);
</script>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
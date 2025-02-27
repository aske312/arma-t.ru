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

    <!-- Боковое меню категорий -->

    <!--
    <div class="catalog-sidebar-m">
        <h2>Разделы</h2>

        <ul id="catalog-menu" class="catalog-menu-m">
            <?php if (!empty($arResult['SECTIONS'])): ?>
                <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                    <?php $isActive = ($arSection['ID'] == $sectionId) ? 'active' : ''; ?>
                    <li>
                        <div class="category-block-m <?= $isActive; ?>" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                            <?php if ($arSection['PICTURE']): ?>
                                <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                                <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                            <?php else: ?>
                                <img alt="Нет изображения" src="/resources/img/no_image.png">
                            <?php endif; ?>
                            <div class="category-text-m"> <?= $arSection['NAME']; ?> </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div> -->

    <!-- Выпадающее боковое меню -->
    <div class="catalog-sidebar-m">
        <div class="sidebar-toggle-m" onclick="toggleSidebar()">
            <span>Разделы</span>
        </div>

        <div id="sidebar-content" class="sidebar-content-m">
            <ul id="catalog-menu" class="catalog-menu-m">
                <?php if (!empty($arResult['SECTIONS'])): ?>
                    <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                        <?php $isActive = ($arSection['ID'] == $sectionId) ? 'active' : ''; ?>
                        <li>
                            <div class="category-block-m <?= $isActive; ?>" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                                <?php if ($arSection['PICTURE']): ?>
                                    <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                                    <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                                <?php else: ?>
                                    <img alt="Нет изображения" src="/resources/img/no_image.png">
                                <?php endif; ?>
                                <div class="category-text-m"><?= $arSection['NAME']; ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="catalog-content-m">
        <!-- Заголовок для фильтров (кнопка) -->
        <button class="filter-title-m" onclick="toggleFilters()">
            Фильтры  <span id="filters-arrow">▼</span>
        </button>

        <!-- Фильтры -->
        <div class="catalog-filters-m">
            <?php foreach ($filterValues as $propertyCode => $values): ?>
                <?php if (empty($values)) continue; ?> <!-- Если значений нет, пропускаем этот фильтр -->
                <div class="filter-m">
                    <!-- Фильтр для диаметра -->
                    <?php if ($propertyCode == 'EL_DN_DIAMETER_MM'): ?>
                        <div class="filter-item-m">
                            <label for="<?= $propertyCode ?>" class="filter-label-m">Диаметр DN:</label>
                            <div class="filter-content-m">
                                <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                    <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                    <?php foreach ($values as $value): ?>
                                        <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="unit-m">мм</span>
                            </div>
                        </div>
                    <?php elseif ($propertyCode == 'EL_PN_PRESSURE_KGF_CM2'): ?>
                        <!-- Фильтр для давления -->
                        <div class="filter-item-m">
                            <label for="<?= $propertyCode ?>" class="filter-label-m">Давление PN:</label>
                            <div class="filter-content-m">
                                <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                    <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                    <?php foreach ($values as $value): ?>
                                        <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="unit-m">кгс/см²</span>
                            </div>
                        </div>
                    <?php elseif ($propertyCode == 'EL_CONNECTION_TYPE'): ?>
                        <!-- Фильтр для типа соединения -->
                        <div class="filter-item-m">
                            <label for="<?= $propertyCode ?>" class="filter-label-m">Тип присоединения:</label>
                            <div class="filter-content-m">
                                <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                    <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                    <?php foreach ($values as $value): ?>
                                        <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php elseif ($propertyCode == 'EL_DRIVE_TYPE'): ?>
                        <!-- Фильтр для типа привода -->
                        <div class="filter-item-m">
                            <label for="<?= $propertyCode ?>" class="filter-label-m">Тип привода:</label>
                            <div class="filter-content-m">
                                <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                    <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                    <?php foreach ($values as $value): ?>
                                        <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php elseif ($propertyCode == 'EL_BODY_MATERIAL'): ?>
                        <!-- Фильтр для материала корпуса -->
                        <div class="filter-item-m">
                            <label for="<?= $propertyCode ?>" class="filter-label-m">Материал корпуса:</label>
                            <div class="filter-content-m">
                                <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                    <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                    <?php foreach ($values as $value): ?>
                                        <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php elseif ($propertyCode == 'EL_FIGURE_TABLE'): ?>
                        <!-- Фильтр для таблиц фигур -->
                        <div class="filter-item-m">
                            <label for="<?= $propertyCode ?>" class="filter-label-m">Таблица фигур:</label>
                            <div class="filter-content-m">
                                <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                    <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                    <?php foreach ($values as $value): ?>
                                        <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Список элементов каталога -->
        <div class="catalog-items">
            <?php
            $itemsFound = false;

            // Внутри цикла, где формируются элементы каталога
            while ($ob = $res->GetNextElement()):
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();
                foreach ($arResult['SECTIONS'] as $arSection):
                    if ($arSection['ID'] == $arFields['IBLOCK_SECTION_ID']):
                        $itemsFound = true;

                        // Получаем путь к изображению
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

                        <div class="catalog-item" data-id="<?= $arFields['ID']; ?>">
                            <!-- Ссылка на детальную страницу -->
                            <a href="detail.php?ID=<?= $arFields['ID']; ?>" class="catalog-item-link">
                                <div class="catalog-item-header">
                                    <img src="<?= $productImage; ?>" alt="<?= $arFields['NAME']; ?>" class="catalog-item-image">
                                    <div class="catalog-item-info">
                                        <h3 class="catalog-item-name"><?= $arFields['PREVIEW_TEXT']; ?></h3>
                                        <p>Артикул: <?= $arProps['EL_ARTICLE']['VALUE']; ?></p>
                                        <p><?= $arProps['EL_PRODUCTION_TIME']['VALUE']; ?></p>
                                        <p>
                                            <div class="catalog-item-price">
                                                <?php $price = $arProps['EL_PURCHASE_PRICE']['VALUE'];
                                                if ($price == 0 || empty($price)) {
                                                    echo 'По запросу';
                                                } else {
                                                    echo $price . ' руб.';
                                                } ?>
                                            </div>
                                        </p>
                                    </div>

                                    <div class="catalog-item-controls">
                                        <button class="catalog-item-add-to-cart"
                                                data-id="<?= $arFields['ID']; ?>"
                                                data-image="<?= $productImage; ?>"
                                                data-name="<?= $arFields['PREVIEW_TEXT']; ?>"
                                                data-price="<?= $arProps['EL_PURCHASE_PRICE']['VALUE']; ?>"
                                                data-article="<?= $arProps['EL_ARTICLE']['VALUE']; ?>">
                                            В корзину
                                        </button>
                                    </div>
                                </div>

                                <!-- Краткое описание элемента -->

                                <!--
                                <div class="catalog-item-properties">
                                    <table>
                                        <?php if (!empty($arProps['EL_CONNECTION_TYPE']['VALUE'])): ?>
                                            <th>Тип присоединения: <?= $arProps['EL_CONNECTION_TYPE']['VALUE']; ?></th>
                                        <?php endif; ?>
                                        <?php if (!empty($arProps['EL_DRIVE_TYPE']['VALUE'])): ?>
                                            <th>Тип привода: <?= $arProps['EL_DRIVE_TYPE']['VALUE']; ?></th>
                                        <?php endif; ?>
                                        <?php if (!empty($arProps['EL_DN_DIAMETER_MM']['VALUE'])): ?>
                                            <th>Диаметр DN: <?= $arProps['EL_DN_DIAMETER_MM']['VALUE']; ?>мм</th>
                                        <?php endif; ?>
                                        <?php if (!empty($arProps['EL_PN_PRESSURE_KGF_CM2']['VALUE'])): ?>
                                            <th>Давление PN: <?= $arProps['EL_PN_PRESSURE_KGF_CM2']['VALUE']; ?>кгс/см²</th>
                                        <?php endif; ?>
                                        <?php if (!empty($arProps['EL_BODY_MATERIAL']['VALUE'])): ?>
                                            <th>Материал корпуса: <?= $arProps['EL_BODY_MATERIAL']['VALUE']; ?></th>
                                        <?php endif; ?>
                                    </table>
                                </div>
                                -->
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endwhile; ?>

            <?php if (!$itemsFound): ?>
                <p>Ничего не найдено</p>
            <?php endif; ?>
        </div>

        <!-- Пагинация -->

        <div class="pagination">
            <?= $arResult['NAV_STRING']; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>

<script>
//    // Функция для выбора всех товаров
//    function toggleSelectAll(checkbox) {
//        const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
//        checkboxes.forEach(item => {
//            item.checked = checkbox.checked;
//        });
//    }
//
//    // Перенаправление на раздел с учетом фильтров
//    function redirectToSection(sectionId) {
//        let urlParams = new URLSearchParams(window.location.search);
//
//        // Добавляем/обновляем параметр SECTION_ID
//        urlParams.set('SECTION_ID', sectionId);
//
//        // Передаем все фильтры
//        const filterProperties = [
//            'EL_CONNECTION_TYPE',
//            'EL_DRIVE_TYPE',
//            'EL_DN_DIAMETER_MM',
//            'EL_PN_PRESSURE_KGF_CM2',
//            'EL_BODY_MATERIAL',
//            'EL_FIGURE_TABLE'
//        ];
//
//        filterProperties.forEach(property => {
//            let filterValue = document.getElementById(property)
//                ? document.getElementById(property).value
//                : 'all';
//            urlParams.set(property, filterValue);
//        });
//
//        window.location.href = '/catalog/index.php?' + urlParams.toString();
//    }
//
//    // Обработка кликов на элемент каталога
//    document.querySelectorAll('.catalog-item').forEach(item => {
//        item.addEventListener('click', (event) => {
//            // Если клик произошел не на кнопке "В корзину", переходим на детальную страницу
//            if (!event.target.closest('.catalog-item-add-to-cart')) {
//                window.location.href = `/catalog/detail.php?ID=${item.getAttribute('data-id')}`;
//            }
//        });
//    });
//
//    // Обработка кликов на кнопки "В корзину"
//    document.querySelectorAll('.catalog-item-add-to-cart').forEach(button => {
//        button.addEventListener('click', (event) => {
//            event.stopPropagation(); // Останавливаем всплытие события
//            event.preventDefault(); // Предотвращаем стандартное поведение (если кнопка внутри <a>)
//
//            // Получаем данные о товаре из атрибутов кнопки
//            const productId = button.getAttribute('data-id');
//            const productImage = button.getAttribute('data-image');
//            const productName = button.getAttribute('data-name');
//            const productPrice = button.getAttribute('data-price');
//            const productArticle = button.getAttribute('data-article');
//
//            // Добавляем товар в корзину
//            addToCart(productId, productImage, productName, productPrice, productArticle);
//
//            // Уведомление пользователя
//            alert('Товар добавлен в корзину');
//        });
//    });
//
//    // Обработка кликов на кнопки "В корзину"
//    document.querySelectorAll('.add-to-cart-button').forEach(button => {
//        button.addEventListener('click', (event) => {
//            event.stopPropagation(); // Останавливаем всплытие
//        });
//    });
//
//    // Переключение отображения фильтров
//    function toggleFilters() {
//        const arrow = document.getElementById('filters-arrow');
//        const filtersContainer = document.querySelector('.catalog-filters');
//
//        if (filtersContainer.style.display === 'none' || filtersContainer.style.display === '') {
//            filtersContainer.style.display = 'flex';
//            arrow.classList.remove('down');
//            arrow.classList.add('up');
//        } else {
//            filtersContainer.style.display = 'none';
//            arrow.classList.remove('up');
//            arrow.classList.add('down');
//        }
//    }
//
//    // Применение фильтров
//    function applyFilter() {
//        let filters = [];
//        let filterValues = {};
//
//        const allFilterProperties = [
//            'EL_CONNECTION_TYPE',
//            'EL_DRIVE_TYPE',
//            'EL_DN_DIAMETER_MM',
//            'EL_PN_PRESSURE_KGF_CM2',
//            'EL_BODY_MATERIAL',
//            'EL_FIGURE_TABLE'
//        ];
//
//        allFilterProperties.forEach(propertyCode => {
//            let element = document.getElementById(propertyCode);
//            let filterValue = element ? element.value : 'all';
//            filterValues[propertyCode] = filterValue;
//            filters.push(propertyCode + '=' + (filterValue || 'all'));
//        });
//
//        let searchElement = document.getElementById('search');
//        let searchQuery = searchElement ? searchElement.value.trim() : '';
//        filters.push('search=' + encodeURIComponent(searchQuery || ''));
//
//        let urlParams = new URLSearchParams(window.location.search);
//
//        // Добавляем текущий SECTION_ID
//        <?php if (isset($_GET['SECTION_ID'])): ?>
//            urlParams.set('SECTION_ID', '<?= $_GET['SECTION_ID'] ?>');
//        <?php endif; ?>
//
//        filters.forEach(filter => {
//            let [key, value] = filter.split('=');
//            urlParams.set(key, value);
//        });
//
//        window.location.search = urlParams.toString();
//    }
//
//    // Логика работы корзины
//    const EXPIRY_DAYS = 3;
//
//    function setCartItemsToStorage(cartItems) {
//        const now = Date.now();
//        const data = {
//            cartItems: cartItems,
//            expiry: now + EXPIRY_DAYS * 24 * 60 * 60 * 1000
//        };
//        localStorage.setItem('cartItems', JSON.stringify(data));
//    }
//
//    function getCartItemsFromStorage() {
//        const data = JSON.parse(localStorage.getItem('cartItems'));
//        return data ? data.cartItems : [];
//    }
//
//    // Функция добавления товара в корзину
//    function addToCart(productId, productImage, productName, productPrice, productArticle) {
//        let cartItems = getCartItemsFromStorage();
//        let found = false;
//
//        cartItems.forEach(item => {
//            if (item.id === productId) {
//                item.quantity += 1;
//                found = true;
//            }
//        });
//
//        if (!found) {
//            cartItems.push({
//                id: productId,
//                image: productImage,
//                name: productName,
//                price: productPrice,
//                article: productArticle,
//                quantity: 1
//            });
//        }
//
//        setCartItemsToStorage(cartItems);
//        updateCartCounter();
//    }
//
//    // Функция обновления счетчика товаров в корзине
//    function updateCartCounter() {
//        const cartItems = getCartItemsFromStorage();
//        const itemCount = cartItems.reduce((total, item) => total + item.quantity, 0);
//        document.getElementById('cart-count').textContent = itemCount;
//    }
//
//    // Добавление всех выбранных товаров в корзину
//    document.querySelector('.catalog-add-all').addEventListener('click', function () {
//        const selectedItems = document.querySelectorAll('.catalog-item-checkbox:checked');
//        let cartItems = getCartItemsFromStorage();
//
//        selectedItems.forEach(item => {
//            const productId = item.dataset.id;
//            const productImage = item.dataset.image;
//            const productName = item.dataset.name;
//            const productPrice = item.dataset.price;
//            const productArticle = item.dataset.article;
//
//            let existingItem = cartItems.find(i => i.id === productId);
//            if (existingItem) {
//                existingItem.quantity += 1;
//            } else {
//                cartItems.push({
//                    id: productId,
//                    image: productImage,
//                    name: productName,
//                    price: productPrice,
//                    article: productArticle,
//                    quantity: 1
//                });
//            }
//        });
//
//        setCartItemsToStorage(cartItems);
//        updateCartCounter();
//    });
//
//    // Обработчики для детальных ссылок
//    document.querySelectorAll('.catalog-item-link').forEach(link => {
//        link.addEventListener('click', event => {
//            const buttonClicked = event.target.closest('.catalog-item-add-to-cart');
//            if (!buttonClicked) {
//                return;
//            }
//        });
//    });
//
//    // Инициализация
//    document.addEventListener('DOMContentLoaded', updateCartCounter);

    document.addEventListener("DOMContentLoaded", function () {
        loadCart(); // Загружаем корзину при загрузке страницы
    });

    function toggleSidebar() {
        let sidebarContent = document.getElementById("sidebar-content");

        if (sidebarContent.style.left === "-250px" || sidebarContent.style.left === "") {
            sidebarContent.style.left = "0";
        } else {
            sidebarContent.style.left = "-250px";
        }
    }

    function redirectToSection(sectionId) {
        window.location.href = `/catalog/index.php?SECTION_ID=${sectionId}`;
    }

    function toggleFilters() {
        let filters = document.querySelector(".catalog-filters-m");
        let arrow = document.getElementById("filters-arrow");

        if (filters.style.display === "none" || filters.style.display === "") {
            filters.style.display = "flex";
            arrow.innerHTML = "▲";
        } else {
            filters.style.display = "none";
            arrow.innerHTML = "▼";
        }
    }

    function applyFilter() {
        let params = new URLSearchParams(window.location.search);
        document.querySelectorAll(".catalog-filters-m select").forEach(select => {
            params.set(select.name, select.value);
        });
        window.location.search = params.toString();
    }

    function redirectToSection(sectionId) {
        window.location.href = `/catalog/index.php?SECTION_ID=${sectionId}`;
    }

    document.querySelectorAll(".catalog-item-add-to-cart-m").forEach(button => {
        button.addEventListener("click", function (event) {
            event.stopPropagation(); // Останавливаем всплытие события, чтобы не перейти на детальную страницу

            let item = {
                id: this.getAttribute("data-id"),
                image: this.getAttribute("data-image"),
                name: this.getAttribute("data-name"),
                price: this.getAttribute("data-price"),
                article: this.getAttribute("data-article"),
                quantity: 1
            };

            addToCart(item);
        });
    });

    function addToCart(item) {
        let cartData = localStorage.getItem("cartItems");
        let cart = cartData ? JSON.parse(cartData) : { cartItems: [] };

        let existingItem = cart.cartItems.find(cartItem => cartItem.id === item.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.cartItems.push(item);
        }

        localStorage.setItem("cartItems", JSON.stringify(cart));
        loadCart();
    }

    function loadCart() {
        let cartDataString = localStorage.getItem("cartItems");
        let cartIcon = document.getElementById("cart-count");

        if (!cartDataString) {
            cartIcon.style.display = "none";
            return;
        }

        let cartData = JSON.parse(cartDataString);
        if (!cartData || !Array.isArray(cartData.cartItems) || cartData.cartItems.length === 0) {
            cartIcon.style.display = "none";
            return;
        }

        let totalCount = cartData.cartItems.reduce((sum, item) => sum + item.quantity, 0);
        cartIcon.innerText = totalCount;
        cartIcon.style.display = "block";
    }
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
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
    'EL_CONNTYPE' => [],
    'EL_DRIVETYPE' => [],
    'EL_DN_DIAMETER_MM' => [],
    'EL_PN_PRESSURE_KGF_CM2' => [],
    'EL_FIGTABLE' => [],
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
$filterProperties = ['EL_CONNTYPE', 'EL_DRIVETYPE', 'EL_DN_DIAMETER_MM', 'EL_PN_PRESSURE_KGF_CM2', 'EL_BODY_MATERIAL', 'EL_FIGTABLE'];
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

<!-- Названия каталога и описания -->
<div class="section-title">
    <h2><?= isset($selectedSection['NAME']) ? $selectedSection['NAME'] : 'Применен фильтр'; ?></h2>
    <p><?= isset($selectedSection['DESCRIPTION']) && !empty($selectedSection['DESCRIPTION']) ? $selectedSection['DESCRIPTION'] : 'Выберете необходимые позиции'; ?></p>
</div>

<div class="catalog-container">

    <!-- Боковое меню категорий -->
    <div class="catalog-sidebar">
        <h2>Разделы</h2>

        <ul id="catalog-menu" class="catalog-menu">
            <!-- Кнопка для сброса фильтрации по секции (Все позиции) -->
<!--
            <li>
                <div class="category-block <?= ($sectionId === 'all') ? 'active' : ''; ?>"
                     onclick="redirectToSection('all')">
                    <img alt="Все позиции" src="/resources/img/all_positions.png">
                    <div class="category-text">Все позиции</div>
                </div>
            </li>
-->
            <?php if (!empty($arResult['SECTIONS'])): ?>
                <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                    <?php $isActive = ($arSection['ID'] == $sectionId) ? 'active' : ''; ?>
                    <li>
                        <div class="category-block <?= $isActive; ?>" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                            <?php if ($arSection['PICTURE']): ?>
                                <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                                <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                            <?php else: ?>
                                <img alt="Нет изображения" src="/resources/img/no_image.png">
                            <?php endif; ?>
                            <div class="category-text"> <?= $arSection['NAME']; ?> </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="catalog-content">

        <!-- Заголовок для фильтров -->
        <h2 class="filter-title">Фильтры</h2>

        <!-- Фильтры -->
        <div class="catalog-filters">
            <?php
            $filters = [
                'EL_CONNTYPE' => ['label' => 'Тип присоединения', 'suffix' => ''],
                'EL_DRIVETYPE' => ['label' => 'Тип привода', 'suffix' => ''],
                'EL_BODY_MATERIAL' => ['label' => 'Материал корпуса', 'suffix' => ''],
                'EL_FIGTABLE' => ['label' => 'Таблица фигур', 'suffix' => ''],
                'EL_DN_DIAMETER_MM' => ['label' => 'Диаметр DN', 'suffix' => 'мм'],
                'EL_PN_PRESSURE_KGF_CM2' => ['label' => 'Давление PN', 'suffix' => 'кгс/см²']
            ];

            foreach ($filters as $propertyCode => $filter):
                if (empty($filterValues[$propertyCode])) continue;
            ?>
                <div class="filter">
                    <div class="filter-item">
                        <div class="dropdown">
                            <button class="dropdown-toggle" onclick="toggleDropdown('filter<?= $propertyCode ?>')">
                                <?= $filter['label'] ?>
                                <span id="filter<?= $propertyCode ?>-counter" class="counter"></span>
                                <span id="filter<?= $propertyCode ?>-arrow" class="arrow">▼</span>
                            </button>
                            <span class="clear-filter" onclick="clearFilter('filter<?= $propertyCode ?>')" style="display: none;">✖</span>
                            <div id="filter<?= $propertyCode ?>" class="dropdown-content">
                                <div class="dropdown-items">
                                    <?php foreach ($filterValues[$propertyCode] as $value): ?>
                                        <label class="dropdown-item">
                                            <input type="checkbox" name="<?= $propertyCode ?>[]" value="<?= $value ?>"
                                                   onchange="updateCounter('filter<?= $propertyCode ?>')"
                                                   <?= (isset($_GET[$propertyCode]) && in_array($value, (array)$_GET[$propertyCode])) ? 'checked' : ''; ?>>
                                            <?= $value ?> <?= $filter['suffix'] ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <button class="apply-button" id="applyFilterButton-<?= $propertyCode ?>" onclick="applyFilter('<?= $propertyCode ?>')" style="display: none;">Показать</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Анимация загрузки -->
        <div id="loader" class="loader" style="display: none;">Загрузка...</div>

        <!-- Чекбокс для выбора всех товаров -->
        <div class="select-all">
            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
            <label for="select-all">Выбрать все</label>
            <button class="catalog-add-all">В корзину</button>
        </div>

        <!-- Список элементов каталога -->
        <div class="catalog-items">
            <?php
            $itemsFound = false;

            while ($ob = $res->GetNextElement()):
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();
                foreach ($arResult['SECTIONS'] as $arSection):
                    if ($arSection['ID'] == $arFields['IBLOCK_SECTION_ID']):
                        $itemsFound = true;
                        ?>
                        <div class="catalog-item" data-id="<?= $arFields['ID']; ?>">

                            <!-- Ссылка на детальную страницу -->
                            <a href="detail.php?id=<?= $arFields['ID']; ?>" class="catalog-item-link">
                                <div class="catalog-item-header">
                                    <input type="checkbox" class="catalog-item-checkbox" id="item-<?= $arFields['ID']; ?>">

                                    <?php
                                    if ($arFields['PICTURE']) {
                                        $imgPath = CFile::GetPath($arFields['PICTURE']);
                                    } elseif ($arFields['PREVIEW_PICTURE']) {
                                        $imgPath = CFile::GetPath($arFields['PREVIEW_PICTURE']);
                                    } elseif ($arSection['PICTURE']) {
                                        // Один вызов CFile::GetPath для картинки раздела
                                        $imgPath = CFile::GetPath($arSection['PICTURE']);
                                    } else {
                                        $imgPath = "/resources/img/no_image.png";
                                    }
                                    ?>

                                    <img src="<?= $imgPath; ?>" alt="<?= $arFields['NAME']; ?>" class="catalog-item-image">
                                    <div class="catalog-item-info">
                                        <h3 class="catalog-item-name"><?= !empty($arFields['PREVIEW_TEXT']) ? $arFields['PREVIEW_TEXT'] : 'Нет анонса'; ?></h3>
                                        <p>Артикул: <?= $arProps['EL_ARTICLE_CODE']['VALUE']; ?></p>
                                        <p><?= $arProps['EL_PRODUCTION_TIME']['VALUE']; ?></p>
                                        <p><div class="catalog-item-price">
                                            <?php $price = $arProps['EL_PURCHASE_PRICE']['VALUE'];

                                            if ($price == 0 || empty($price)) {
                                                echo 'По запросу';
                                            } else {
                                                echo $price . ' руб.';
                                            } ?>
                                        </div></p>
                                    </div>

                                    <div class="catalog-item-controls">
                                        <button class="catalog-item-add-to-cart"
                                                data-id="<?= $arFields['ID']; ?>"
                                                data-name="<?= $arFields['PREVIEW_TEXT']; ?>"
                                                data-price="<?= $arProps['EL_PRICE']['VALUE']; ?>"
                                                data-article="<?= $arProps['EL_ARTICLE_CODE']['VALUE']; ?>">В корзину</button>
                                    </div>
                                </div>

                                <!-- Краткое описание элемента -->
                                <div class="catalog-item-properties">
                                    <table>
                                        <?php if (!empty($arProps['EL_CONNTYPE']['VALUE'])): ?>
                                            <th>Тип присоединения: <?= $arProps['EL_CONNTYPE']['VALUE']; ?></th>
                                        <?php endif; ?>
                                        <?php if (!empty($arProps['EL_DRIVETYPE']['VALUE'])): ?>
                                            <th>Тип привода: <?= $arProps['EL_DRIVETYPE']['VALUE']; ?></th>
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
                            </a>
                        </div>
                    <?php endif; ?><?php endforeach; ?>
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

    // Функция для выбора всех товаров
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
        checkboxes.forEach(item => {
            item.checked = checkbox.checked;
        });
    }

    function redirectToSection(sectionId) {
        let urlParams = new URLSearchParams(window.location.search);

        // Добавляем/обновляем параметр SECTION_ID
        urlParams.set('SECTION_ID', sectionId);

        // Передаем все фильтры
        const filterProperties = ['EL_CONNTYPE', 'EL_DRIVETYPE', 'EL_DN_DIAMETER_MM', 'EL_PN_PRESSURE_KGF_CM2', 'EL_BODY_MATERIAL', 'EL_FIGTABLE'];

        filterProperties.forEach(function (property) {
            var filterValue = document.getElementById(property) ? document.getElementById(property).value : 'all';
            urlParams.set(property, filterValue);
        });

        window.location.href = '/catalog/index.php?' + urlParams.toString();
    }

    document.querySelectorAll('.catalog-item').forEach(item => {
        item.addEventListener('click', (event) => {
            if (!event.target.closest('.add-to-cart-button')) { // Предотвращаем переход на детальную с кнопки "в корзину"
                window.location.href = `/catalog/detail.php?id=${item.getAttribute('data-id')}`;
            }
        });
    });

    document.querySelectorAll('.add-to-cart-button').forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation(); // Останавливаем всплытие
            // Логика добавления в корзину
        });
    });

    // *** FILTERS *** //

    // Функция для открытия/закрытия выпадающего списка
    function toggleDropdown(id) {
        const dropdownContent = document.getElementById(id);
        const arrow = document.getElementById(id + '-arrow');

        dropdownContent.classList.toggle('show');
        arrow.classList.toggle('up');

        // Закрытие списка при клике вне элемента
        document.addEventListener('click', function handleClickOutside(event) {
            if (!dropdownContent.contains(event.target) && !event.target.matches('.dropdown-toggle')) {
                dropdownContent.classList.remove('show');
                arrow.classList.remove('up');
                document.removeEventListener('click', handleClickOutside);
            }
        });
    }

    // Функция для обновления счетчика и отображения кнопки "Показать"
    function updateCounter(dropdownId) {
        const checkboxes = document.querySelectorAll(`#${dropdownId} input[type="checkbox"]`);
        const selectedCount = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;
        const counter = document.getElementById(`${dropdownId}-counter`);
        const applyButton = document.querySelector(`#${dropdownId} .apply-button`);
        const clearFilter = document.querySelector(`#${dropdownId} .clear-filter`);

        // Обновляем счетчик
        counter.innerText = selectedCount > 0 ? selectedCount : '';

        // Показываем/скрываем кнопку "Показать"
        if (applyButton) {
            applyButton.style.display = selectedCount > 0 ? 'inline' : 'none';
        }

        // Показываем/скрываем крестик для очистки
        if (clearFilter) {
            clearFilter.style.display = selectedCount > 0 ? 'inline' : 'none';
        }
    }

    // Функция для применения фильтра
    function applyFilter(propertyCode) {
        let filters = [];
        let filterValues = {};

        const checkboxes = document.querySelectorAll(`input[name="${propertyCode}[]"]`);
        const selectedValues = Array.from(checkboxes).filter(checkbox => checkbox.checked).map(checkbox => checkbox.value);

        if (selectedValues.length > 0) {
            filterValues[propertyCode] = selectedValues;
            filters.push(propertyCode + '=' + encodeURIComponent(selectedValues.join(',')));
        } else {
            filterValues[propertyCode] = 'all';
            filters.push(propertyCode + '=all');
        }

        // Перезагружаем страницу с новыми параметрами
        const urlParams = new URLSearchParams(window.location.search);
        filters.forEach(function (filter) {
            const [key, value] = filter.split('=');
            urlParams.set(key, value);
        });
        window.location.search = urlParams.toString();
    }

    // Очистка фильтра
    function clearFilter(dropdownId) {
        const checkboxes = document.querySelectorAll(`#${dropdownId} input[type="checkbox"]`);
        checkboxes.forEach(checkbox => checkbox.checked = false);
        updateCounter(dropdownId); // Обновляем отображение кнопки и счетчика
    }

    // *** ЛОГИКА КОРЗИНЫ *** //
    const EXPIRY_DAYS = 3;

    // Функция для сохранения товаров в localStorage
    function setCartItemsToStorage(cartItems) {
        const now = new Date().getTime();
        const data = {
            cartItems: cartItems,
            expiry: now + (3 * 24 * 60 * 60 * 1000)  // 3 дня в миллисекундах
        };
        localStorage.setItem('cartItems', JSON.stringify(data));
    }

    // Функция для получения товаров из localStorage
    function getCartItemsFromStorage() {
        const data = JSON.parse(localStorage.getItem('cartItems'));
        return data ? data.cartItems : [];
    }

    // Функция добавления товара в корзину
    function addToCart(productId, productName, productPrice, productArticle) {
        let cartItems = getCartItemsFromStorage();
        let found = false;

        cartItems.forEach(item => {
            if (item.id === productId) {
                item.quantity += 1;
                found = true;
            }
        });

        if (!found) {
            cartItems.push({
                id: productId,
                name: productName,
                price: productPrice,
                article: productArticle,  // Добавляем артикул
                quantity: 1
            });
        }

        setCartItemsToStorage(cartItems);
        updateCartCounter();
    }

    // Функция обновления счетчика товаров в корзине
    function updateCartCounter() {
        const cartItems = getCartItemsFromStorage();
        const itemCount = cartItems.reduce((total, item) => total + item.quantity, 0);
        document.getElementById('cart-count').textContent = itemCount;  // Обновляем отображение счетчика
    }

    document.querySelector('.catalog-add-all').addEventListener('click', function() {
        const selectedItems = document.querySelectorAll('.catalog-item-checkbox:checked');
        let cartItems = getCartItemsFromStorage();

        selectedItems.forEach(item => {
            const productId = item.dataset.id;
            const productName = item.dataset.name;
            const productPrice = item.dataset.price;
            const productArticle = item.dataset.article;

            let existingItem = cartItems.find(i => i.id === productId);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cartItems.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    article: productArticle,
                    quantity: 1
                });
            }
        });

        setCartItemsToStorage(cartItems);
        updateCartCounter();
    });

    // Обработчики для кнопок "В корзину"
    document.querySelectorAll('.catalog-item-add-to-cart').forEach(button => {
        button.addEventListener('click', event => {
            event.stopPropagation(); // Останавливаем распространение события
            event.preventDefault(); // Останавливаем переход по ссылке

            // Получаем данные из атрибутов кнопки
            const productId = button.getAttribute('data-id');
            const productName = button.getAttribute('data-name');
            const productPrice = button.getAttribute('data-price');
            const productArticle = button.getAttribute('data-article'); // Артикул товара

            // Вызываем функцию добавления товара в корзину
            addToCart(productId, productName, productPrice, productArticle);
        });
    });

    // Функция добавления товара в корзину
    function addToCart(productId, productName, productPrice, productArticle) {
        let cartItems = getCartItemsFromStorage();  // Получаем текущие товары в корзине
        let found = false;

        // Проверяем, есть ли товар в корзине
        cartItems.forEach(item => {
            if (item.id === productId) {
                item.quantity += 1; // Если товар уже есть, увеличиваем количество
                found = true;
            }
        });

        if (!found) {
            // Если товара нет в корзине, добавляем его
            cartItems.push({
                id: productId,
                name: productName,
                price: productPrice,
                article: productArticle,  // Добавляем артикул
                quantity: 1
            });
        }

        setCartItemsToStorage(cartItems);  // Сохраняем корзину в localStorage
        updateCartCounter();  // Обновляем счетчик товаров в корзине
    }

    // Находим все ссылки на детальные страницы и добавляем обработку кликов только для них
    document.querySelectorAll('.catalog-item-link').forEach(function(link) {
        link.addEventListener('click', function(event) {
            // Если клик не на кнопке "В корзину", переходим по ссылке
            const buttonClicked = event.target.closest('.catalog-item-add-to-cart');
            if (!buttonClicked) {
                return; // Если это не кнопка "В корзину", переходить по ссылке
            }
        });
    });

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', updateCartCounter);
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>

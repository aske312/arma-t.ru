<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Каталог");

// Получаем ID секции
$sectionId = intval($_GET['SECTION_ID']);
if (empty($sectionId)) {
    $sectionId = 1;
}

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss("/resources/css/catalog.css"); // Подключаем CSS

// Фильтр для текущей секции
$sectionFilter = [
    'IBLOCK_ID' => 1,
    'ID' => $sectionId,
    'ACTIVE' => 'Y',
];
$selectedSection = CIBlockSection::GetList([], $sectionFilter, false, ['ID', 'NAME', 'DESCRIPTION'])->Fetch();

// Получение списка секций для бокового меню
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

// Фильтры
$filterValues = [
    'EL_CONNTYPE' => [],
    'EL_DRIVETYPE' => [],
    'EL_DN_DIAMETER_MM' => [],
    'EL_PN_PRESSURE_KGF_CM2' => [],
    'EL_BODY_MATERIAL' => []
];

// Получаем все элементы в текущей секции
$filterElementSelect = ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'PROPERTY_*'];
$res = CIBlockElement::GetList(
    [],
    [
        'IBLOCK_ID' => 1,  // Указываем ID инфоблока
        'SECTION_ID' => $sectionId,  // ID секции
        'ACTIVE' => 'Y',  // Только активные элементы
        'INCLUDE_SUBSECTIONS' => 'Y',  // Включаем подкатегории
    ],
    false,
    false,
    $filterElementSelect  // Получаем только необходимые свойства
);

// Перебираем все элементы
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();       // Получаем поля элемента
    $arProps = $ob->GetProperties();    // Получаем все свойства элемента

    // Проходим по необходимым свойствам и собираем уникальные значения
    foreach (['EL_CONNTYPE', 'EL_DRIVETYPE', 'EL_DN_DIAMETER_MM', 'EL_PN_PRESSURE_KGF_CM2', 'EL_BODY_MATERIAL'] as $propertyCode) {
        // Проверяем, если свойство существует и содержит значения
        if (isset($arProps[$propertyCode]) && !empty($arProps[$propertyCode]['VALUE'])) {
            // Убедимся, что это массив, если нет - сделаем его массивом
            $values = is_array($arProps[$propertyCode]['VALUE']) ? $arProps[$propertyCode]['VALUE'] : [$arProps[$propertyCode]['VALUE']];

            // Добавляем уникальные значения в массив фильтров
            foreach ($values as $value) {
                // Проверяем, есть ли уже это значение в массиве фильтров
                if (!in_array($value, $filterValues[$propertyCode])) {
                    $filterValues[$propertyCode][] = $value;
                }
            }
        }
    }
}

// Убираем дубли и сортируем значения для каждого фильтра
foreach ($filterValues as $propertyCode => $values) {
    $filterValues[$propertyCode] = array_unique($values); // Убираем дубли
    sort($filterValues[$propertyCode]); // Сортируем для удобства
}

// Получаем список элементов с учетом фильтров и пагинации
$elementFilter = [
    'IBLOCK_ID' => 1,
    'SECTION_ID' => $sectionId,
    'ACTIVE' => 'Y',
    'INCLUDE_SUBSECTIONS' => 'Y',
];

$filterProperties = ['EL_CONNTYPE', 'EL_DRIVETYPE', 'EL_DN_DIAMETER_MM', 'EL_PN_PRESSURE_KGF_CM2', 'EL_BODY_MATERIAL'];

// Применяем фильтры из GET-запроса
foreach ($filterProperties as $propertyCode) {
    if (isset($_GET[$propertyCode]) && $_GET[$propertyCode] !== 'all') {
        $elementFilter['PROPERTY_' . $propertyCode] = $_GET[$propertyCode];
    }
}

$elementSelect = ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'PROPERTY_*'];
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

        <!-- Заголовок для фильтров -->
        <h2 class="filter-title">Фильтры</h2>

        <!-- Фильтры -->
        <div class="catalog-filters">
            <?php foreach ($filterValues as $propertyCode => $values): ?>
                <?php if (empty($values)) continue; ?> <!-- Если значений нет, пропускаем этот фильтр -->
                <div class="filter">
                    <!-- Фильтр для диаметра -->
                    <?php if ($propertyCode == 'EL_DN_DIAMETER_MM'): ?>
                        <div class="filter-item">
                            <label for="<?= $propertyCode ?>" class="filter-label">Диаметр DN:</label>
                            <div class="filter-content">
                                <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                    <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                    <?php foreach ($values as $value): ?>
                                        <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="unit">мм</span>
                            </div>
                        </div>
                    <?php elseif ($propertyCode == 'EL_PN_PRESSURE_KGF_CM2'): ?>
                        <!-- Фильтр для давления -->
                        <div class="filter-item">
                            <label for="<?= $propertyCode ?>" class="filter-label">Давление PN:</label>
                            <div class="filter-content">
                                <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                                    <option value="all" <?= (empty($_GET[$propertyCode]) || $_GET[$propertyCode] == 'all') ? 'selected' : ''; ?>>Все</option>
                                    <?php foreach ($values as $value): ?>
                                        <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>>
                                            <?= $value ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="unit">кгс/см²</span>
                            </div>
                        </div>
                    <?php elseif ($propertyCode == 'EL_CONNTYPE'): ?>
                        <!-- Фильтр для типа соединения -->
                        <div class="filter-item">
                            <label for="<?= $propertyCode ?>" class="filter-label">Тип присоединения:</label>
                            <div class="filter-content">
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
                    <?php elseif ($propertyCode == 'EL_DRIVETYPE'): ?>
                        <!-- Фильтр для типа привода -->
                        <div class="filter-item">
                            <label for="<?= $propertyCode ?>" class="filter-label">Тип привода:</label>
                            <div class="filter-content">
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
                        <div class="filter-item">
                            <label for="<?= $propertyCode ?>" class="filter-label">Материал корпуса:</label>
                            <div class="filter-content">
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
            $itemsFound = false; // Флаг, чтобы проверить, были ли элементы

            while ($ob = $res->GetNextElement()):
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();
                foreach ($arResult['SECTIONS'] as $arSection):
                    if ($arSection['ID'] == $arFields['IBLOCK_SECTION_ID']):
                        $itemsFound = true; // Если элемент найден, меняем флаг
                        ?>
                        <div class="catalog-item" data-id="<?= $arFields['ID']; ?>">

                            <!-- Ссылка на детальную страницу -->
                            <a href="detail.php?id=<?= $arFields['ID']; ?>" class="catalog-item-link">
                                <div class="catalog-item-header">
                                    <input type="checkbox" class="catalog-item-checkbox" id="item-<?= $arFields['ID']; ?>">
                                    <?php if ($arSection['PICTURE']): ?>
                                        <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                                        <img src="<?= $imgPath; ?>" alt="<?= $arSection['NAME']; ?>" class="catalog-item-image">
                                    <?php else: ?>
                                        <img alt="Нет изображения" src="/resources/img/no_image.png">
                                    <?php endif; ?>

                                    <div class="catalog-item-info">
                                        <h3 class="catalog-item-name"><?= !empty($arFields['PREVIEW_TEXT']) ? $arFields['PREVIEW_TEXT'] : 'Нет анонса'; ?></h3>
                                        <p>Артикул: <?= $arProps['EL_ARTICLE_CODE']['VALUE']; ?></p>
                                        <p><?= $arProps['EL_PRODUCTION_TIME']['VALUE']; ?></p>
                                        <p><div class="catalog-item-price">
                                            <?php $price = $arProps['EL_PURCHASE_PRICE']['VALUE'];

                                            // Проверяем цену, если 0 или не задана, выводим текст
                                            if ($price == 0 || empty($price)) {
                                                echo 'По запросу';
                                            } else {
                                                echo $price . ' руб.';
                                            }?>
                                        </div></p>
                                    </div>

                                    <!-- Кнопка "В корзину" -->
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
                                        <th>Тип присоединения: <?= $arProps['EL_CONNTYPE']['VALUE']; ?></th>
                                        <th>Тип привода: <?= $arProps['EL_DRIVETYPE']['VALUE']; ?></th>
                                        <th>Диаметр DN: <?= $arProps['EL_DN_DIAMETER_MM']['VALUE']; ?>мм</th>
                                        <th>Давление PN: <?= $arProps['EL_PN_PRESSURE_KGF_CM2']['VALUE']; ?>кгс/см²</th>
                                        <th>Материал корпуса: <?= $arProps['EL_BODY_MATERIAL']['VALUE']; ?></th>
                                    </table>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?><?php endforeach; ?>
            <?php endwhile; ?>

            <!-- Если элементы не найдены, выводим сообщение -->
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

    // Функция перехода на каталог по секциям
    function redirectToSection(sectionId) {
        window.location.href = '/catalog/index.php?SECTION_ID=' + sectionId;
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
    function applyFilter() {
        let filters = [];
        let filterValues = {};

        // Собираем текущие фильтры
        <?php foreach ($filterProperties as $propertyCode): ?>
            filterValues['<?= $propertyCode ?>'] = document.getElementById('<?= $propertyCode ?>').value;

            // Если выбрано "Все", добавляем параметр в виде propertyCode=all
            if (filterValues['<?= $propertyCode ?>'] === 'all') {
                filters.push('<?= $propertyCode ?>=all');
            } else if (filterValues['<?= $propertyCode ?>'] !== 'all') {
                // Добавляем только те фильтры, которые не равны "all"
                filters.push('<?= $propertyCode ?>=' + filterValues['<?= $propertyCode ?>']);
            }
        <?php endforeach; ?>

        // Получаем текущие параметры URL
        let urlParams = new URLSearchParams(window.location.search);

        // Добавляем SECTION_ID, если он присутствует
        <?php if (isset($_GET['SECTION_ID'])): ?>
            urlParams.set('SECTION_ID', '<?= $_GET['SECTION_ID'] ?>');
        <?php endif; ?>

        // Добавляем фильтры в URL
        filters.forEach(function (filter) {
            let [key, value] = filter.split('=');
            urlParams.set(key, value);
        });

        // Перезагружаем страницу с новыми параметрами
        window.location.search = urlParams.toString();
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

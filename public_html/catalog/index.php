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

// Получаем значения фильтров из свойств текущих элементов
$filterProperties = ['EL_CONNTYPE', 'EL_DRIVETYPE', 'EL_DN_DIAMETER_MM', 'EL_PN_PRESSURE_KGF_CM2', 'EL_BODY_MATERIAL'];
$filterValues = [];

// Собираем уникальные значения свойств для фильтров
foreach ($filterProperties as $propertyCode) {
    $filterValues[$propertyCode] = [];
    $res = CIBlockElement::GetList(
        [],
        [
            'IBLOCK_ID' => 1,
            'SECTION_ID' => $sectionId,
            'ACTIVE' => 'Y',
            'INCLUDE_SUBSECTIONS' => 'Y',
        ],
        false,
        false,
        ['PROPERTY_' . $propertyCode]
    );
    while ($ob = $res->GetNextElement()) {
        $props = $ob->GetProperties();
        if (isset($props[$propertyCode]) && is_array($props[$propertyCode]['VALUE'])) {
            foreach ($props[$propertyCode]['VALUE'] as $value) {
                $filterValues[$propertyCode][] = $value;
            }
        } elseif (isset($props[$propertyCode]['VALUE'])) {
            $filterValues[$propertyCode][] = $props[$propertyCode]['VALUE'];
        }
    }

    // Убираем дубли
    $filterValues[$propertyCode] = array_unique($filterValues[$propertyCode]);
}

// Получаем список элементов с учетом фильтров и пагинации
$elementFilter = [
    'IBLOCK_ID' => 1,
    'SECTION_ID' => $sectionId,
    'ACTIVE' => 'Y',
    'INCLUDE_SUBSECTIONS' => 'Y',
];

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
            <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                <li>
                    <div class="category-block" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                        <img alt="<?= $arSection['NAME']; ?>" src="<?= CFile::GetPath($arSection['PICTURE']); ?>">
                        <div class="category-text"><?= $arSection['NAME']; ?></div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="catalog-content">

        <!-- Фильтры -->
        <div class="catalog-filters">
            <?php foreach ($filterProperties as $propertyCode): ?>
                <div class="filter">
                    <label for="<?= $propertyCode ?>"><?= GetMessage($propertyCode); ?></label>
                    <select id="<?= $propertyCode ?>" name="<?= $propertyCode ?>" onchange="applyFilter()">
                        <option value="all">Все</option>
                        <?php foreach ($filterValues[$propertyCode] as $value): ?>
                            <option value="<?= $value ?>" <?= ($_GET[$propertyCode] == $value) ? 'selected' : ''; ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Список элементов каталога -->
        <div class="catalog-items">
            <?php while ($ob = $res->GetNextElement()): ?>
                <?php
                    $arFields = $ob->GetFields();
                    $arProps = $ob->GetProperties();
                ?>
                <div class="catalog-item">
                    <a href="detail.php?id=<?= $arFields['ID']; ?>" class="catalog-item-link">
                        <div class="catalog-item-header">
                            <img src="<?= CFile::GetPath($arFields['PREVIEW_PICTURE']); ?>" alt="<?= $arFields['NAME']; ?>" class="catalog-item-image">
                            <div class="catalog-item-info">
                                <h3 class="catalog-item-name"><?= $arFields['NAME']; ?></h3>
                                <p>Артикул: <?= $arProps['EL_ARTICLE_CODE']['VALUE']; ?></p>
                                <p><?= $arProps['EL_PRODUCTION_TIME']['VALUE']; ?></p>
                                <p><?= $arProps['EL_PURCHASE_PRICE']['VALUE']; ?> руб.</p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
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

    // Функция для применения фильтра
    function applyFilter() {
        let filters = [];
        <?php foreach ($filterProperties as $propertyCode): ?>
            let value = document.getElementById('<?= $propertyCode ?>').value;
            if (value !== 'all') {
                filters.push('<?= $propertyCode ?>=' + value);
            }
        <?php endforeach; ?>

        // Перезагружаем страницу с новыми параметрами фильтра
        window.location.search = filters.join('&');
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

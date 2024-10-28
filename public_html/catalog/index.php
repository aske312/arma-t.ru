<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Каталог");

//session_start()

// Берем значение секции
$sectionId = intval($_GET['SECTION_ID']);
if (empty($sectionId)) {
    $sectionId = 2;
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
    [$arParams['ELEMENT_SORT_FIELD'] => $arParams['ELEMENT_SORT_ORDER']],
    $elementFilter,
    false,
    ['nPageSize' => 10],  // Ограничение вывода до 10 позиций
    $elementSelect
);

$res->NavStart(10); // Устанавливаем навигацию с количеством элементов на страницу
$arResult['NAV_STRING'] = $res->GetPageNavStringEx($navComponentObject, "", ".default"); // Генерация строки навигации

$cartItemCount = "<script>document.write(localStorage.getItem('cartItems') ? JSON.parse(localStorage.getItem('cartItems')).reduce((acc, item) => acc + item.quantity, 0) : 0);</script>";
$cartItems = isset($_SESSION['cartItems']['cartItems']) ? $_SESSION['cartItems']['cartItems'] : []; // Получаем массив товаров
?>

                <div class="cart-wrapper">
                    <div class="cart-icon">
                        <button id="cart-button" class="cart-btn">
                            В Корзине (<span id="cart-count"><?= $cartItemCount ?></span>)
                        </button>
                    </div>

                    <div id="cart-modal" class="cart-modal">
                        <div class="cart-modal-content">
                            <span class="close-btn" id="close-cart-modal">&times;</span>
                            <h2>Товары в корзине</h2>
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Цена за ед.</th>
                                        <th>Количество</th>
                                        <th>Общая цена</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-items"></tbody>
                            </table>
                            <div id="cart-total"></div>
                            <button id="clear-cart" class="button">Очистить корзину</button>
                            <button id="checkout" class="button">Оформить заказ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

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
                foreach ($arResult['SECTIONS'] as $arSection):
                if ($arSection['ID'] == $arFields['IBLOCK_SECTION_ID']): ?>
                <div class="catalog-item" data-id="<?= $arFields['ID']; ?>">
                    <div class="catalog-item-header">
                        <input type="checkbox" class="catalog-item-checkbox" id="item-<?= $arFields['ID']; ?>">
                        <?php if ($arSection['PICTURE']): ?>
                        <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                        <img src="<?= $imgPath; ?>" alt="<?= $arSection['NAME']; ?>" class="catalog-item-image">
                        <?php else: ?>
                        <img alt="Нет изображения" src="/resources/img/no_image.png">
                        <?php endif; ?>
                        <div class="catalog-item-info">
                            <h3 class="catalog-item-name"><?= $arFields['NAME']; ?></h3>
                            <p>Артикул: <?= $arProps['EL_ARTICUL']['VALUE']; ?></p>
                            <p><?= $arProps['EL_AVAILABILITY']['VALUE']; ?></p>
                            <p><div class="catalog-item-price"><?= $arProps['EL_PRICE']['VALUE']; ?> руб.</div></p>
                        </div>
                        <!-- Кнопка "В корзину" -->
                        <div class="catalog-item-controls">
                            <button class="catalog-item-add-to-cart" data-id="<?= $arFields['ID']; ?>" data-name="<?= $arFields['NAME']; ?>" data-price="<?= $arProps['EL_PRICE']['VALUE']; ?>">В корзину</button>
                        </div>
                    </div>

                    <!-- Краткое описание элемента -->
                    <div class="catalog-item-properties">
                        <table>
                            <th>Тип присоединения: <?= $arProps['EL_TYPE_S']['VALUE']; ?></th>
                            <th>Тип привода: <?= $arProps['EL_TYPE_P']['VALUE']; ?></th>
                            <th>Диаметр DN: <?= $arProps['EL_DIAMETR']['VALUE']; ?>мм</th>
                            <th>Давление PN: <?= $arProps['EL_DOWN']['VALUE']; ?>кгс/см²</th>
                            <th>Материал корпуса: <?= $arProps['EL_MATERIAL']['VALUE']; ?></th>
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

        <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>
        <script>

            // Получаем данные корзины из localStorage
            function getCartItems() {
                const storedData = JSON.parse(localStorage.getItem('cartItem'));
                return storedData && storedData.cartItems ? storedData.cartItems : [];
            }

            // Функция для сохранения данных корзины в localStorage
            function setCartItems(cartItems) {
                const expiryDate = Date.now() + 3 * 24 * 60 * 60 * 1000; // срок хранения - 3 дня
                const cartData = { cartItems, expiry: expiryDate };
                localStorage.setItem('cartItems', JSON.stringify(cartData));
            }

            // Функция для обновления количества товаров в корзине
            function updateCartCount() {
                const cartItems = getCartItems();
                const count = cartItems.reduce((total, item) => total + item.quantity, 0);
                document.getElementById('cart-count').textContent = count;
            }

            // Функция для загрузки и отображения данных корзины в модальном окне
            function loadCartData() {
                const cartItems = getCartItems();
                const cartItemsContainer = document.getElementById('cart-items');
                cartItemsContainer.innerHTML = ''; // Очищаем содержимое перед добавлением новых данных
                let totalSum = 0;

                if (cartItems.length === 0) {
                    cartItemsContainer.innerHTML = '<tr><td colspan="5">Корзина пуста</td></tr>';
                    document.getElementById('cart-total').innerText = 'Общая сумма: 0.00 руб.';
                    return;
                }

                cartItems.forEach(item => {
                    const row = `
                        <tr>
                            <td title="${item.name}">${item.name}</td>
                            <td>${item.price} руб.</td>
                            <td>
                                <button class="quantity-btn" onclick="updateQuantity('${item.id}', -1)">&#8722;</button>
                                <input type="number" class="quantity-input" value="${item.quantity}" onchange="updateQuantityManual('${item.id}', this.value)">
                                <button class="quantity-btn" onclick="updateQuantity('${item.id}', 1)">&#43;</button>
                            </td>
                            <td>${(item.price * item.quantity).toFixed(2)} руб.</td>
                            <td><button class="remove-item-btn" onclick="removeCartItem('${item.id}')">&#10005;</button></td>
                        </tr>
                    `;
                    cartItemsContainer.innerHTML += row;
                    totalSum += item.price * item.quantity;
                });

                document.getElementById('cart-total').innerText = `Общая сумма: ${totalSum.toFixed(2)} руб.`;
            }

            // Остальной код для обновления, ручного ввода и удаления товаров не изменялся

            // Обработка кнопок открытия и закрытия модального окна
            document.getElementById('cart-button').addEventListener('click', function() {
                const cartModal = document.getElementById('cart-modal');
                cartModal.style.display = cartModal.style.display === 'block' ? 'none' : 'block';
                loadCartData(); // Загружаем данные корзины при открытии
            });

            document.getElementById('close-cart-modal').addEventListener('click', function() {
                document.getElementById('cart-modal').style.display = 'none';
            });

            // Инициализация и обновление количества товаров при загрузке страницы
            updateCartCount();

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

            // *** ЛОГИКА КОРЗИНЫ *** //
            const EXPIRY_DAYS = 3;

            // Функция сохранения данных корзины в localStorage с сроком истечения
            function setCartItemsToStorage(cartItems) {
                const now = new Date().getTime();
                const data = {
                    cartItems: cartItems,
                    expiry: now + (EXPIRY_DAYS * 24 * 60 * 60 * 1000) // 3 дня в миллисекундах
                };
                localStorage.setItem('cartItems', JSON.stringify(data));
            }

            // Функция получения товаров из localStorage с проверкой срока годности
            function getCartItemsFromStorage() {
                const data = JSON.parse(localStorage.getItem('cartItems'));
                if (!data) return [];
                const now = new Date().getTime();
                if (now > data.expiry) {
                    localStorage.removeItem('cartItems');
                    return [];
                }
                return data.cartItems;
            }

            // Функция добавления товара в корзину
            function addToCart(productId, productName, productPrice) {
                let cartItems = getCartItemsFromStorage();
                let found = false;

                cartItems.forEach(item => {
                    if (item.id === productId) {
                        item.quantity += 1; // Увеличиваем количество
                        found = true;
                    }
                });

                if (!found) {
                    cartItems.push({
                        id: productId,
                        name: productName,
                        price: productPrice,
                        quantity: 1
                    });
                }

                setCartItemsToStorage(cartItems);
                updateCartCounter();
            }

            // Обновление счетчика товаров в корзине
            function updateCartCounter() {
                const cartItems = getCartItemsFromStorage();
                const itemCount = cartItems.reduce((total, item) => total + item.quantity, 0);
                document.getElementById('cart-count').textContent = itemCount;
            }

            // Инициализация обработчиков событий для кнопок "В корзину"
            document.querySelectorAll('.catalog-item-add-to-cart').forEach(button => {
                button.addEventListener('click', event => {
                    event.stopPropagation(); // Останавливаем переход на детальную страницу
                    const productId = button.getAttribute('data-id');
                    const productName = button.getAttribute('data-name');
                    const productPrice = button.getAttribute('data-price');
                    addToCart(productId, productName, productPrice);
                });
            });

            // Инициализация при загрузке страницы
            document.addEventListener('DOMContentLoaded', updateCartCounter);
        </script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
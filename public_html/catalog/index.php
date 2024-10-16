<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Каталог");

// Берем значение секции
$sectionId = intval($_GET['SECTION_ID']);
if (empty($sectionId)) {
    $sectionId = 2;
}

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss("/local/css/catalog.css"); //css

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
                    <?php else: ?><img alt="Нет изображения" src="/local/img/no_image.png"><?php endif; ?>
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
                        <img alt="Нет изображения" src="/local/img/no_image.png">
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
                            <th>Тип присоединения: <?= $arProps['EL_CONNECTION_TYPE']['VALUE']; ?></th>
                            <th>Тип привода: <?= $arProps['EL_DRIVE_TYPE']['VALUE']; ?></th>
                            <th>Диаметр DN: <?= $arProps['EL_DIAMETER_DN']['VALUE']; ?>мм</th>
                            <th>Давление PN: <?= $arProps['EL_PRESSURE_PN']['VALUE']; ?>кгс/см²</th>
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

            //*** КОРЗИНА ***//
            // Обработка клика на блок товара
            document.querySelectorAll('.catalog-item').forEach(function(item) {
                item.addEventListener('click', function(event) {
                    // Если клик произошел не на кнопку "В корзину", переход на детальную страницу
                    if (!event.target.classList.contains('catalog-item-add-to-cart')) {
                        var productId = this.getAttribute('data-id');
                        window.location.href = 'detail.php?id=' + productId;
                    }
                });
            });

            // Обработка клика на кнопку "В корзину"
            document.querySelectorAll('.catalog-item-add-to-cart').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.stopPropagation(); // Останавливаем всплытие события клика, чтобы не было перехода на детальную страницу
                    var productId = this.getAttribute('data-id');
                    var productName = this.getAttribute('data-name');
                    var productPrice = this.getAttribute('data-price');
                    addToCart(productId, productName, productPrice);
                });
            });

            // Добавление всех выбранных товаров в корзину по чекбоксам
            document.querySelector('.catalog-add-all').addEventListener('click', function() {
                var selectedItems = document.querySelectorAll('.catalog-item-checkbox:checked');

                selectedItems.forEach(function(checkbox) {
                    var productId = checkbox.getAttribute('data-id');
                    var productElement = checkbox.closest('.catalog-item');
                    var productName = productElement.querySelector('.catalog-item-add-to-cart').getAttribute('data-name');
                    var productPrice = productElement.querySelector('.catalog-item-add-to-cart').getAttribute('data-price');

                    addToCart(productId, productName, productPrice);
                });
            });

            // Функция добавления товара в корзину
            function addToCart(productId, productName, productPrice) {
                var cartItems = getCartItemsFromCookie();
                var found = false;

                // Проверяем, есть ли товар в корзине
                for (var i = 0; i < cartItems.length; i++) {
                    if (cartItems[i].id === productId) {
                        cartItems[i].quantity += 1; // Увеличиваем количество, если товар уже в корзине
                        found = true;
                        break;
                    }
                }

                if (!found) {
                    // Если товара еще нет в корзине, добавляем его с количеством 1
                    cartItems.push({
                        id: productId,
                        name: productName,
                        price: productPrice,
                        quantity: 1
                    });
                }

                // Сохраняем обновленную корзину в куки
                saveCartItemsToCookies(cartItems);
                updateCartCounter();
            }

            // Получение товаров из куки
            function getCartItemsFromCookie() {
                var cartItems = Cookies.get('cartItems');
                return cartItems ? JSON.parse(cartItems) : [];
            }

            // Сохранение товаров в куки
            function saveCartItemsToCookies(cartItems) {
                Cookies.set('cartItems', JSON.stringify(cartItems), { expires: 7 });
            }

            // Обновление счетчика товаров в корзине
            function updateCartCounter() {
                var cartItems = getCartItemsFromCookie();
                var itemCount = cartItems.reduce(function(total, item) {
                    return total + item.quantity;
                }, 0);

                document.getElementById('cart-count').textContent = itemCount;
            }



                        // Обработчик для открытия и закрытия модального окна корзины
            document.getElementById('cart-button').addEventListener('click', function() {
                var cartModal = document.getElementById('cart-modal');
                var buttonRect = this.getBoundingClientRect(); // Получаем координаты кнопки

                // Устанавливаем позицию окна относительно кнопки
                cartModal.style.top = (buttonRect.bottom + window.scrollY + 5) + 'px'; // На 5px ниже кнопки
                cartModal.style.left = (buttonRect.left + window.scrollX - (cartModal.offsetWidth / 2) + (buttonRect.width / 2)) + 'px';

                // Переключаем отображение окна
                if (cartModal.style.display === 'none' || cartModal.style.display === '') {
                    cartModal.style.display = 'block';
                } else {
                    cartModal.style.display = 'none';
                }

                // Загрузка данных корзины
                loadCartData();
            });

            // Закрытие модального окна корзины по крестику
            document.getElementById('close-cart-modal').addEventListener('click', function() {
                var cartModal = document.getElementById('cart-modal');
                cartModal.style.display = 'none';
            });

            // Функция загрузки товаров из куки в модальное окно корзины
            function loadCartData() {
                var cartItems = getCartItemsFromCookie();

                if (cartItems.length > 0) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'get_product_data.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (Array.isArray(response)) {
                                updateCart(response);  // Функция обновления содержимого корзины
                            }
                        }
                    };
                    xhr.send('cartItems=' + encodeURIComponent(JSON.stringify(cartItems)));
                } else {
                    document.getElementById('cart-items').innerHTML = '<p>Ваша корзина пуста</p>';
                }
            }

            // Функция обновления содержимого корзины
            function updateCart(basketData) {
                var cartItemsContainer = document.getElementById('cart-items');
                cartItemsContainer.innerHTML = '';
                var totalSum = 0;

                basketData.forEach(function(item, index) {
                    var itemRow = document.createElement('div');
                    itemRow.className = 'cart-item';
                    itemRow.innerHTML = `
                        <div>№${index + 1}</div>
                        <div>Название: ${item.name}</div>
                        <div>Цена: ${item.price}</div>
                        <div>Количество: ${item.quantity}</div>
                        <div>Сумма: ${item.total}</div>
                        <button class="remove-item-btn" data-id="${item.id}">Удалить</button>
                    `;
                    cartItemsContainer.appendChild(itemRow);

                    totalSum += item.total;
                });

                document.getElementById('cart-total').innerHTML = `Общая сумма: ${totalSum} руб.`;

                // Обработчик удаления товара из корзины
                document.querySelectorAll('.remove-item-btn').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var productId = this.getAttribute('data-id');
                        removeCartItem(productId);
                    });
                });
            }

            // Функция получения товаров из куки
            function getCartItemsFromCookie() {
                var cartItems = [];
                var cookies = document.cookie.split(';');

                cookies.forEach(function(cookie) {
                    var cookiePair = cookie.split('=');
                    if (cookiePair[0].trim() === 'cartItems') {
                        cartItems = JSON.parse(decodeURIComponent(cookiePair[1]));
                    }
                });

                return cartItems;
            }

            // Функция удаления товара из корзины
            function removeCartItem(productId) {
                var cartItems = getCartItemsFromCookie();
                cartItems = cartItems.filter(function(item) {
                    return item.id !== productId;
                });
                document.cookie = 'cartItems=' + encodeURIComponent(JSON.stringify(cartItems)) + ';path=/';
                loadCartData();  // Обновляем корзину
            }

            // Обработчик очистки корзины
            document.getElementById('clear-cart').addEventListener('click', function() {
                document.cookie = 'cartItems=; Max-Age=-99999999;';  // Очищаем куки
                loadCartData();  // Обновляем корзину
            });

            // Обработчик оформления заказа
            document.getElementById('checkout').addEventListener('click', function() {
                window.location.href = '/checkout/';  // Переход на страницу оформления заказа
            });
        </script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
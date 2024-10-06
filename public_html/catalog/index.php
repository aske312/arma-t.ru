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

            <!-- Боковое меню категорий -->
            <div class="catalog-sidebar">
                <ul id="catalog-menu" class="catalog-menu">
                    <?php if (!empty($arResult['SECTIONS'])): ?>
                    <?php foreach ($arResult['SECTIONS'] as $arSection): ?>
                    <li>
                        <div class="category-block" onclick="redirectToSection(<?= $arSection['ID']; ?>)">
                            <?php if ($arSection['PICTURE']): ?>
                            <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                            <img alt="<?= $arSection['NAME']; ?>" src="<?= $imgPath; ?>">
                            <?php else: ?><img alt="Нет изображения" src="/local/img/no_image.png"><?php endif; ?>
                            <div class="category-text"> <?= $arSection['NAME']; ?> </div>
                        </div>
                    </li><?php endforeach; ?><?php endif; ?>
                </ul>
            </div>

            <!-- Основная часть каталога -->
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
                            </th><?php endforeach; ?><?php endif; ?>
                        </table>
                        <button type="submit">Применить фильтр</button>
                    </form>
                </div> -->

                <!-- Чекбокс для выбора всех товаров -->
                <div class="select-all">
                    <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
                    <label for="select-all">Выбрать все</label>
                    <button class="catalog-add-all" onclick="addSelectedToCart()">В корзину</button>
                </div>

                <!-- Список элементов каталога  -->
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
                            <input type="checkbox" class="catalog-item-checkbox" data-id="<?= $arFields['ID']; ?>" data-articul="<?= $arProps['EL_ARTICUL']['VALUE']; ?>" data-price="<?= $arProps['EL_PRICE']['VALUE']; ?>">
                            <?php if ($arSection['PICTURE']): ?>
                            <?php $imgPath = CFile::GetPath($arSection['PICTURE']); ?>
                            <img src="<?= $imgPath; ?>" alt="<?= $arSection['NAME']; ?>" class="catalog-item-image">
                            <?php else: ?>
                            <img alt="Нет изображения" src="/local/img/no_image.png">
                            <?php endif; ?>
                            <div class="catalog-item-info">
                                <h3 class="catalog-item-name"><?= $arFields['NAME']; ?></h3>
                                <p>Артикул: <?= $arProps['EL_ARTICUL']['VALUE']; ?>
                                <span><?= $arProps['EL_AVAILABILITY']['VALUE']; ?></p>
                                <p><div class="catalog-item-price"><?= $arProps['EL_PRICE']['VALUE']; ?> руб.</div></p>
                            </div>
                            <div class="catalog-item-controls">
                                <!-- <button class="catalog-item-add-to-cart" onclick="addToCart(<?= $arFields['ID']; ?>, '<?= $arProps['EL_SH_NAME']['VALUE']; ?>', <?= $arProps['EL_PRICE']['VALUE']; ?>)">В корзину</button> -->
                                <!-- <button class="catalog-item-add-to-cart" data-id="<?= $arFields['ID']; ?>" data-article="<?= $arProps['EL_SH_NAME']['VALUE']; ?>"  data-price="<?= $arProps['EL_PRICE']['VALUE']; ?>">В корзину</button> -->
                                <button class="catalog-item-add-to-cart" data-id="<?= $arFields['ID']; ?>">В корзину</button>
                            </div>
                        </div>

                        <!-- Краткое описание элемента  -->
                        <div class="catalog-item-properties">
                            <table>
                                <th>Тип присоединения: <?= $arProps['EL_CONNECTION_TYPE']['VALUE']; ?></th>
                                <th>Тип привода: <?= $arProps['EL_DRIVE_TYPE']['VALUE']; ?></th>
                                <th>Тип затвора: <?= $arProps['EL_TYPE_ZATVOR']['VALUE']; ?></th>
                                <th>Материал корпуса: <?= $arProps['EL_BODY_MATERIAL']['VALUE']; ?></th>
                            </table>
                        </div>

                    </div><?php endif; ?><?php endforeach; ?><?php endwhile; ?>

                    <!-- Пагинация -->
                    <?php if ($res->SelectedRowsCount() > 0): ?>
                    <div class="pagination">
                        <?= $arResult['NAV_STRING']; ?>
                    </div><?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            // Функции плавующего меню
            function toggleMenu() {
                var nav = document.getElementById('mainNav');
                nav.classList.toggle('menu-open');
            }

            window.onscroll = function() {stickyHeader()};

            var header = document.getElementById("siteHeader");
            var sticky = header.offsetTop;

            function stickyHeader() {
                if (window.pageYOffset > sticky) {
                    header.classList.add("fixed");
                } else {
                    header.classList.remove("fixed");
                }
            }

            // Функция для выбора всех товаров
            function toggleSelectAll(checkbox) {
                const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
                checkboxes.forEach(item => {
                    item.checked = checkbox.checked;
                });
            }

            // Функция перехода на каталог по секциям
            function redirectToSection(sectionId) {
                window.location.href = '/catalog/catalog.php?SECTION_ID=' + sectionId;
            }

            //************ Корзина товаров **************//
            // Добавление товара в корзину
            document.querySelectorAll('.catalog-item-add-to-cart').forEach(function (button) {
                button.addEventListener('click', function () {
                    var productId = this.getAttribute('data-id');
                    addToCart(productId);
                });
            });

            // Добавление товара в корзину (AJAX)
            function addToCart(productId) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'add_to_cart.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        updateCart(JSON.parse(xhr.responseText));
                    }
                };
                xhr.send('id=' + productId);
            }

            // Обновление корзины
            function updateCart(basketData) {
                var basketItems = document.getElementById('basket-items');
                basketItems.innerHTML = '';

                var totalPrice = 0;

                basketData.forEach(function (item) {
                    var row = document.createElement('div');
                    row.classList.add('basket-item');
                    row.innerHTML = `
                        <div class="item-details">
                            <img src="${item.picture}" alt="${item.name}" class="item-picture">
                            <h4 class="item-name">${item.name}</h4>
                            <p class="item-price">Цена: ${item.price} руб.</p>
                            <p class="item-quantity">Количество: <input type="number" value="${item.quantity}" min="1" class="quantity" data-id="${item.id}"></p>
                            <p class="item-total-price">Стоимость: ${item.price * item.quantity} руб.</p>
                        </div>
                    `;
                    basketItems.appendChild(row);

                    totalPrice += item.price * item.quantity;
                });

                document.getElementById('total-price').textContent = totalPrice;

                // Изменение количества товара
                document.querySelectorAll('.quantity').forEach(function (input) {
                    input.addEventListener('change', function () {
                        var productId = this.getAttribute('data-id');
                        var quantity = this.value;
                        updateQuantity(productId, quantity);
                    });
                });
            }

            // Обновление количества товара (AJAX)
            function updateQuantity(productId, quantity) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_quantity.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        updateCart(JSON.parse(xhr.responseText));
                    }
                };
                xhr.send('id=' + productId + '&quantity=' + quantity);
            }

            // Очистка корзины
            document.getElementById('clear-cart').addEventListener('click', function () {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'clear_cart.php', true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        updateCart([]);
                    }
                };
                xhr.send();
            });

            // Оформление заказа
            document.getElementById('checkout').addEventListener('click', function () {
                window.location.href = '/checkout/';
            });
        </script>

<!-- FOOTER -->
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
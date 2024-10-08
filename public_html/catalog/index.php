<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

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

            //*** КОРЗИНА ***//
            document.querySelectorAll('.catalog-item-add-to-cart').forEach(function (button) {
                button.addEventListener('click', function () {
                    var productId = this.getAttribute('data-id');
                    addToCart(productId);
                });
            });

            function addToCart(productId) {
                // Получаем текущие товары из куки
                var cart = JSON.parse(getCookie('cart') || '[]');

                // Проверяем, есть ли товар уже в корзине
                if (!cart.includes(productId)) {
                    cart.push(productId); // Добавляем ID в корзину
                    setCookie('cart', JSON.stringify(cart), 7); // Сохраняем куки на 7 дней
                    updateCartCounter();
                }
            }

            function updateCartCounter() {
                var cart = JSON.parse(getCookie('cart') || '[]');
                var counter = cart.length;
                document.getElementById('cart-counter').innerText = counter;
            }

            // Функция для получения куки
            function getCookie(name) {
                var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                return match ? match[2] : null;
            }

            // Функция для установки куки
            function setCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }

            // Обновляем счетчик сразу при загрузке страницы
            updateCartCounter();
        </script>

<!-- FOOTER -->
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
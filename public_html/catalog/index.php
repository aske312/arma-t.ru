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
        'SECTION_ID' => $section['ID'], // Исправлено для корректного получения всех элементов
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

// Убираем SECTION_ID, чтобы получить все элементы
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
$arResult['NAV_STRING'] = $res->GetPageNavStringEx($navComponentObject, "", ".default");


//echo '<pre>'; // Открываем тег <pre> для форматированного вывода
//
//while ($ob = $res->GetNextElement()) {
//    $arFields = $ob->GetFields();      // Основные поля элемента
//    $arProps = $ob->GetProperties();   // Свойства элемента
//
//    echo "Элемент ID: " . $arFields['ID'] . "\n";
//    echo "Название: " . $arFields['NAME'] . "\n";
//
//    echo "\nПоля элемента:\n";
//    print_r($arFields); // Вывод всех полей элемента
//
//    echo "\nСвойства элемента:\n";
//    print_r($arProps); // Вывод всех свойств элемента
//
//    echo "\n------------------------\n"; // Разделитель для удобства
//}
//
//echo '</pre>'; // Закрываем тег <pre>

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

                <!-- Фильтры -->
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
                </div>

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
                    <div class="catalog-item" onclick="redirectToDetail(<?= $arSection['ID']; ?>, <?= $arFields['ID']; ?>)">
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
                                <button class="catalog-item-add-to-cart" onclick="addToCart(<?= $arFields['ID']; ?>, '<?= $arProps['EL_ARTICUL']['VALUE']; ?>', <?= $arProps['EL_PRICE']['VALUE']; ?>)">В корзину</button>
                            </div>
                        </div>

                        <!-- Краткое описание элемента  -->
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
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php endwhile; ?>
                </div>

                <!-- Пагинация  -->
                <div class="pagination">
                    <?= $arResult['NAV_STRING']; ?>
                </div>
            </div>
        </div>
        <script>
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

            document.getElementById("catalog-button").addEventListener("click", function() {
                var catalogMenu = document.getElementById("catalog-menu");
                if (catalogMenu.style.display === "block") {
                    catalogMenu.style.display = "none";
                } else {
                    catalogMenu.style.display = "block";
                }
            });

            // Функция перехода на детальную страницу
            function redirectToDetail(sectionId, elementId) {
                window.location.href = '/catalog/detail.php?SECTION_ID=' + sectionId + '&ID=' + elementId;
            }

            // Функция применения фильтра
            function applyFilter(property, value) {
                console.log(`Применен фильтр: ${property} = ${value}`);
                // Здесь можно добавить логику для фильтрации элементов каталога на странице
            }

            // Функция выбора/отмены всех товаров
            function toggleSelectAll(checkbox) {
                var checkboxes = document.querySelectorAll(".catalog-item-checkbox");
                checkboxes.forEach(function(cb) {
                    cb.checked = checkbox.checked;
                });
            }

            document.addEventListener("DOMContentLoaded", function() {
                const categoryButtons = document.querySelectorAll('.catalog-category-btn');
                const catalogItems = document.querySelector('.catalog-items');
                const sectionTitle = document.getElementById('section-title');

                categoryButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const sectionId = this.dataset.sectionId;

                        // Обновление заголовка раздела
                        sectionTitle.textContent = this.textContent;

                        // Очистка текущих товаров
                        catalogItems.innerHTML = '';

                        // AJAX-запрос для получения товаров выбранной категории
                        fetch('index.php?SECTION_ID=' + sectionId)
                            .then(response => response.text())
                            .then(data => {
                                catalogItems.innerHTML = data;
                            })
                            .catch(error => console.error('Error:', error));
                    });
                });
            });

            //// Корзина catalog.php
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

            // Переключение отображения корзины
            function toggleBasketDropdown() {
                const basketDropdown = document.getElementById('basketDropdown');
                basketDropdown.style.display = basketDropdown.style.display === 'none' ? 'block' : 'none';
            }

            // Функция для добавления выбранных товаров в корзину
            document.querySelector('.catalog-add-all').addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.catalog-item-checkbox:checked');
                checkboxes.forEach(function(checkbox) {
                    const itemId = checkbox.id.replace('item-', '');
                    addToCart(itemId);
                });
            });

            // Функция для выделения всех товаров
            function toggleSelectAll(source) {
                const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
                checkboxes.forEach(checkbox => checkbox.checked = source.checked);
            }

            // Функция для добавления товара в корзину
            function addToCart(itemId) {
                const basket = getBasket();
                const item = basket.find(i => i.id === itemId);

                if (item) {
                    item.count++;
                } else {
                    basket.push({ id: itemId, count: 1 });
                }

                saveBasket(basket);
                updateBasketUI();
            }

            // Получение корзины из LocalStorage
            function getBasket() {
                const basket = localStorage.getItem('basket');
                return basket ? JSON.parse(basket) : [];
            }

            // Сохранение корзины в LocalStorage
            function saveBasket(basket) {
                localStorage.setItem('basket', JSON.stringify(basket));
            }

            // Очистка корзины
            function clearBasket() {
                localStorage.removeItem('basket');
                updateBasketUI();
            }

            // Обновление пользовательского интерфейса корзины
            function updateBasketUI() {
                const basket = getBasket();
                const basketButton = document.getElementById('basketButton');
                const basketCount = document.getElementById('basketCount');
                const basketItems = document.getElementById('basketItems');
                const basketTotal = document.getElementById('basketTotal');

                if (basket.length === 0) {
                    basketButton.style.display = 'none';
                    basketDropdown.style.display = 'none';
                } else {
                    basketButton.style.display = 'block';
                    basketCount.innerText = basket.reduce((sum, item) => sum + item.count, 0);
                    basketItems.innerHTML = basket.map(item => `
                        <div>
                            Товар: ${item.id}, Количество: ${item.count}
                            <button onclick="removeFromCart(${item.id})">-</button>
                            <button onclick="increaseCart(${item.id})">+</button>
                        </div>
                    `).join('');
                    const totalPrice = basket.reduce((sum, item) => sum + (getItemPrice(item.id) * item.count), 0);
                    basketTotal.innerText = 'Общая цена: ' + totalPrice + ' руб.';
                }
            }

            // Увеличение количества товаров в корзине
            function increaseCart(itemId) {
                const basket = getBasket();
                const item = basket.find(i => i.id === itemId);
                if (item) {
                    item.count++;
                    saveBasket(basket);
                    updateBasketUI();
                }
            }

            // Уменьшение количества товаров в корзине
            function removeFromCart(itemId) {
                const basket = getBasket();
                const itemIndex = basket.findIndex(i => i.id === itemId);
                if (itemIndex > -1) {
                    if (basket[itemIndex].count > 1) {
                        basket[itemIndex].count--;
                    } else {
                        basket.splice(itemIndex, 1);
                    }
                    saveBasket(basket);
                    updateBasketUI();
                }
            }

            // Получение цены товара (это просто пример, вместо этого нужно запросить цену из данных товара)
            function getItemPrice(itemId) {
                return 100; // заменить на реальную цену товара
            }

            // Оформление заказа (переход на страницу оформления заказа)
            function checkout() {
                alert('Переход на страницу оформления заказа');
                // Реализуйте переход на страницу оформления заказа
            }

            // Инициализация корзины при загрузке страницы
            document.addEventListener('DOMContentLoaded', updateBasketUI);

            // КОРЗИНА ТОВАРВ catalog.php
            // Переключение отображения корзины
            function toggleBasketDropdown() {
                const basketDropdown = document.getElementById('basketDropdown');
                basketDropdown.style.display = basketDropdown.style.display === 'none' ? 'block' : 'none';
            }

            // Функция для добавления товара в корзину
            function addToCart(itemId) {
                const basket = getBasket();
                const item = basket.find(i => i.id === itemId);

                if (item) {
                    item.count++;
                } else {
                    basket.push({ id: itemId, count: 1 });
                }

                saveBasket(basket);
                updateBasketUI();
            }

            // Получение корзины из LocalStorage
            function getBasket() {
                const basket = localStorage.getItem('basket');
                return basket ? JSON.parse(basket) : [];
            }

            // Сохранение корзины в LocalStorage
            function saveBasket(basket) {
                localStorage.setItem('basket', JSON.stringify(basket));
            }

            // Очистка корзины
            function clearBasket() {
                localStorage.removeItem('basket');
                updateBasketUI();
            }

            // Обновление пользовательского интерфейса корзины
            function updateBasketUI() {
                const basket = getBasket();
                const basketButton = document.getElementById('basketButton');
                const basketCount = document.getElementById('basketCount');
                const basketItems = document.getElementById('basketItems');
                const basketTotal = document.getElementById('basketTotal');

                if (basket.length === 0) {
                    basketButton.style.display = 'none';
                    basketDropdown.style.display = 'none';
                } else {
                    basketButton.style.display = 'block';
                    basketCount.innerText = basket.reduce((sum, item) => sum + item.count, 0);
                    basketItems.innerHTML = basket.map(item => `
                        <div>
                            Товар: ${item.id}, Количество: ${item.count}
                            <button onclick="removeFromCart(${item.id})">-</button>
                            <button onclick="increaseCart(${item.id})">+</button>
                        </div>
                    `).join('');
                    const totalPrice = basket.reduce((sum, item) => sum + (getItemPrice(item.id) * item.count), 0);
                    basketTotal.innerText = 'Общая цена: ' + totalPrice + ' руб.';
                }
            }

            // Увеличение количества товаров в корзине
            function increaseCart(itemId) {
                const basket = getBasket();
                const item = basket.find(i => i.id === itemId);
                if (item) {
                    item.count++;
                    saveBasket(basket);
                    updateBasketUI();
                }
            }

            // Уменьшение количества товаров в корзине
            function removeFromCart(itemId) {
                const basket = getBasket();
                const itemIndex = basket.findIndex(i => i.id === itemId);
                if (itemIndex > -1) {
                    if (basket[itemIndex].count > 1) {
                        basket[itemIndex].count--;
                    } else {
                        basket.splice(itemIndex, 1);
                    }
                    saveBasket(basket);
                    updateBasketUI();
                }
            }

            // Получение цены товара (это просто пример, вместо этого нужно запросить цену из данных товара)
            function getItemPrice(itemId) {
                return 100; // заменить на реальную цену товара
            }

            // Оформление заказа (переход на страницу оформления заказа)
            function checkout() {
                alert('Переход на страницу оформления заказа');
                // Реализуйте переход на страницу оформления заказа
            }

            // Инициализация корзины при загрузке страницы
            document.addEventListener('DOMContentLoaded', updateBasketUI);

            // Функция для добавления выбранных товаров в корзину
            document.querySelector('.catalog-add-all').addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.catalog-item-checkbox:checked');
                checkboxes.forEach(function(checkbox) {
                    const itemId = checkbox.id.replace('item-', '');
                    addToCart(itemId);
                });
            });

            // Функция для выделения всех товаров
            function toggleSelectAll(source) {
                const checkboxes = document.querySelectorAll('.catalog-item-checkbox');
                checkboxes.forEach(checkbox => checkbox.checked = source.checked);
            }
        </script>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

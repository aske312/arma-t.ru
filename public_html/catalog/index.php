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

// Получаем список элементов с учетом фильтров и пагинации
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

        <!-- Чекбокс для выбора всех товаров -->
        <div class="select-all">
            <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
            <label for="select-all">Выбрать все</label>
            <button class="catalog-add-all">В корзину</button>
        </div>

        <!-- Список элементов каталога -->
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
                            <p class="catalog-item-price"><?= $arProps['EL_PURCHASE_PRICE']['VALUE'] ?: 'Цену уточняйте у оператора'; ?> руб.</p>
                        </div>
                    </a>
                    <div class="catalog-item-controls">
                        <button class="catalog-item-add-to-cart" data-id="<?= $arFields['ID']; ?>"
                                data-name="<?= $arFields['NAME']; ?>" data-price="<?= $arProps['EL_PRICE']['VALUE']; ?>"
                                data-article="<?= $arProps['EL_ARTICLE_CODE']['VALUE']; ?>">В корзину</button>
                    </div>
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

    document.addEventListener("DOMContentLoaded", updateCartCounter);
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>

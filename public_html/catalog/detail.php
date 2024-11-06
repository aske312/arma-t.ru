<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/detail.css"); //css

if (CModule::IncludeModule("iblock")) {
    $productId = intval($_GET['id']);
    $res = CIBlockElement::GetByID($productId);

    if ($ar_res = $res->GetNext()) {
        // Получаем основные параметры товара
        $productName = $ar_res['NAME'];
        $productDescription = $ar_res['DETAIL_TEXT'];
        $productImage = CFile::GetPath($ar_res['EL_IMAGES']); // Изображение товара
        $productPrice = ''; // Цена
        $productArticul = ''; // Артикул
        $productAvailability = ''; // Срок изготовления

        // Получаем информацию о разделе
        $sectionId = $ar_res['IBLOCK_SECTION_ID'];
        $sectionRes = CIBlockSection::GetByID($sectionId);
        if ($section = $sectionRes->GetNext()) {
            $sectionImage = CFile::GetPath($section['PICTURE']); // Изображение раздела
            $sectionName = $section['NAME']; // Название раздела
        }

        // Массив для хранения характеристик
        $productProperties = [];

        // Получаем свойства товара
        $properties = CIBlockElement::GetProperty($ar_res['IBLOCK_ID'], $productId, array("sort" => "asc"), array());
        while ($prop = $properties->Fetch()) {
            if (!empty($prop['VALUE'])) {
                // Пропускаем элемент EL_IMAGES
                if ($prop['CODE'] === 'EL_IMAGES') {
                    continue;
                }

                if ($prop['CODE'] === 'EL_ARTICLE_CODE') {
                    $productArticul = $prop['VALUE'];
                } elseif ($prop['CODE'] === 'EL_PRODUCTION_TIME') {
                    $productAvailability = $prop['VALUE'];
                } else {
                    $productProperties[$prop['NAME']] = $prop['VALUE'];
                }
            }
        }

        // Условия для цены
        if (empty($productPrice) || $productPrice == 0) {
            $productPrice = "Под заказ";
        }
?>

        <div class="section-title">
            <h2><?php echo htmlspecialchars($productName); ?></h2> <!-- Название раздела -->
            <p>Подробное описание</p>
        </div>

        <div class="product-detail" style="background-color: white; border-radius: 5px; padding: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
            <div class="product-content">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($productImage ?: $sectionImage); ?>" alt="<?php echo htmlspecialchars($productName); ?>" style="width: 300px; height: auto;"> <!-- Изображение товара -->
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($productName); ?></h1>
                    <div class="product-articul-availability">Артикул: <?php echo htmlspecialchars($productArticul); ?> &nbsp; Срок изготовления: <?php echo htmlspecialchars($productAvailability); ?></div>
                    <!-- <div class="product-availability">Срок изготовления: <?php echo htmlspecialchars($productAvailability); ?></div> -->
                    <div class="product-price">
                        <p>Цена: <?php echo htmlspecialchars($productPrice); ?></p>
                    </div>
                    <button class="add-to-cart">В корзину</button>

                        <div class="catalog-item-controls">
                            <button class="catalog-item-add-to-cart"
                                    data-id="<?= $arFields['ID']; ?>"
                                    data-name="<?= $arFields['NAME']; ?>"
                                    data-price="<?= $arProps['EL_PRICE']['VALUE']; ?>"
                                    data-article="<?= $arProps['EL_ARTICLE_CODE']['VALUE']; ?>">В корзину</button>
                        </div>

                </div>
            </div>
            <?php if (!empty($productProperties)): ?>
                <div class="product-attributes">
                    <h2>Характеристики</h2>
                    <table>
                        <tbody>
                            <?php foreach ($productProperties as $propName => $propValue): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($propName); ?></td>
                                    <td><?php echo htmlspecialchars($propValue); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <button class="back-button" onclick="history.back()">Назад</button> <!-- Кнопка Назад -->
        </div>

<?php
    } else {
        echo "Товар не найден.";
    }
}

<script>

    // Функция для сохранения товаров в localStorage
    function setCartItemsToStorage(cartItems) {
        const now = new Date().getTime();
        const data = {
            cartItems: cartItems,
            expiry: now + (3 * 24 * 60 * 60 * 1000)  // 3 дня в миллисекундах
        };
        localStorage.setItem('cartItems', JSON.stringify(data));
    }

    // Функция обновления счетчика товаров в корзине
    function updateCartCounter() {
        const cartItems = getCartItemsFromStorage();
        const itemCount = cartItems.reduce((total, item) => total + item.quantity, 0);
        document.getElementById('cart-count').textContent = itemCount;  // Обновляем отображение счетчика
    }

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


</script>

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
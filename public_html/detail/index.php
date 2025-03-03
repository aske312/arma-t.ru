<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/detail.css"); // css

// MOBILE VERSION
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $userAgent);

if ($isMobile) {
    include 'm/index.php';
    return;
}

if (CModule::IncludeModule("iblock")) {
    $productId = intval($_GET['ID']);
    $res = CIBlockElement::GetByID($productId);

    if ($ar_res = $res->GetNext()) {
        // Получаем основные параметры товара
        $productName = $ar_res['NAME'];
        $productShortName = $ar_res['PREVIEW_TEXT'];
        $productDescription = $ar_res['DETAIL_TEXT'];
        $productImage = CFile::GetPath($ar_res['PREVIEW_PICTURE']); // Изображение анонса товара
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

        // Если у товара нет изображения, используем изображение раздела
        $productImage = $productImage ?: $sectionImage;

        // Массив для хранения характеристик
        $productProperties = [];

        // Получаем свойства товара
        $properties = CIBlockElement::GetProperty($ar_res['IBLOCK_ID'], $productId, array("sort" => "asc"), array());
        while ($prop = $properties->Fetch()) {
            if (!empty($prop['VALUE'])) {
                // Пропускаем элемент EL_IMAGES
                if ($prop['CODE'] === 'EL_IMAGES') {
                    continue;
                } elseif ($prop['CODE'] === 'EL_SHORT_NAME') {
                    $productShortName = $prop['VALUE'];
                    continue;
                }

                if ($prop['CODE'] === 'EL_ARTICLE') {
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
            $productPrice = "По запросу";
        }
?>

<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(99863715, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/99863715" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<div class="section-title">
    <h2>Подробное описание</h2>
    <p><?php echo htmlspecialchars_decode($productShortName); ?></p>

    <!-- Хлебные крошки -->
    <div class="breadcrumbs">
        <a href="<?php echo htmlspecialchars('/'); ?>">Главная</a> -
        <a href="<?php echo htmlspecialchars('/catalog/'); ?>">Каталог</a> -
        <a href="<?php echo htmlspecialchars('/catalog/index.php' . '?SECTION_ID=' . $sectionId); ?>">
            <?php echo htmlspecialchars_decode($sectionName); ?>
        </a> -
        <?php echo htmlspecialchars_decode($productShortName); ?>
    </div>
</div>

<div class="product-detail" style="background-color: white; border-radius: 5px; padding: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
    <div class="product-content">
        <div class="product-image">
            <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($productName); ?>"> <!-- Изображение товара -->
        </div>
        <div class="product-info">
            <h1><?php echo htmlspecialchars($productName); ?></h1>
            <div class="product-articul-availability">Артикул: <?php echo htmlspecialchars($productArticul); ?> &nbsp; Срок изготовления: <?php echo htmlspecialchars($productAvailability); ?></div>
            <div class="product-price">
                <p>Цена: <?php echo htmlspecialchars($productPrice); ?></p>
            </div>
            <button class="add-to-cart" data-id="<?php echo $productId; ?>"
                    data-image="<?php echo htmlspecialchars($productImage); ?>"
                    data-name="<?php echo htmlspecialchars_decode($productShortName); ?>"
                    data-price="<?php echo htmlspecialchars_decode($productPrice); ?>"
                    data-articul="<?php echo htmlspecialchars_decode($productArticul); ?>"
                    onclick="addToCart(this)">
                В корзину
            </button>
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

<script>
    // Функция добавления товара в корзину
    function addToCart(button) {
        // Получаем данные о товаре из атрибутов кнопки
        const productId = button.getAttribute('data-id');
        const productImage = button.getAttribute('data-image');
        const productName = button.getAttribute('data-name');
        const productPrice = button.getAttribute('data-price');
        const productArticul = button.getAttribute('data-articul');

        // Получаем текущие элементы корзины из localStorage
        let cartItems = JSON.parse(localStorage.getItem('cartItems')) || { cartItems: [], expiry: Date.now() + 3 * 24 * 60 * 60 * 1000 };

        // Проверяем, есть ли уже такой товар в корзине
        let existingItem = cartItems.cartItems.find(item => item.id === productId);

        if (existingItem) {
            // Если товар уже есть, увеличиваем его количество
            existingItem.quantity += 1;
        } else {
            // Если товара нет, добавляем его в корзину
            cartItems.cartItems.push({
                id: productId,
                image: productImage,
                name: productName,
                price: productPrice,
                article: productArticul,
                quantity: 1
            });
        }

        // Обновляем данные в localStorage
        localStorage.setItem('cartItems', JSON.stringify(cartItems));

        // Обновляем счетчик в интерфейсе
        updateCartCounter();

        // Выводим уведомление или обновляем интерфейс
        alert('Товар добавлен в корзину');

        // Обновляем страницу
        location.reload();
    }

    // Функция для обновления счетчика товаров в корзине
    function updateCartCounter() {
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || { cartItems: [] };
        const cartCount = cartItems.cartItems.reduce((total, item) => total + item.quantity, 0);
        document.getElementById('cart-count').innerText = cartCount;
    }

    // Обновляем счетчик при загрузке страницы
    document.addEventListener('DOMContentLoaded', updateCartCounter);
</script>

<?php
    } else {
        echo "Товар не найден.";
    }
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

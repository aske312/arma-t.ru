<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>

<?php
if (CModule::IncludeModule("iblock")) {
    $productId = intval($_GET['id']);
    $res = CIBlockElement::GetByID($productId);

    if ($ar_res = $res->GetNext()) {
        // Получаем основные параметры товара
        $productName = $ar_res['NAME'];
        $productDescription = $ar_res['DETAIL_TEXT'];
        $productImage = CFile::GetPath($ar_res['DETAIL_PICTURE']); // Получаем путь к изображению
        $productPrice = ''; // Здесь можно получить цену, если она хранится в свойствах или в другом инфоблоке

        // Массив для хранения характеристик
        $productProperties = [];

        // Получаем свойства товара
        $properties = CIBlockElement::GetProperty($ar_res['IBLOCK_ID'], $productId, array("sort" => "asc"), array());
        while ($prop = $properties->Fetch()) {
            $productProperties[$prop['NAME']] = $prop['VALUE'];
        }

        // Теперь можно использовать переменные для построения шаблона
        ?>

        <!-- Названия каталога и описания -->
        <div class="section-title">
            <h2><?= isset($selectedSection['NAME']) ? $selectedSection['NAME'] : 'Применен фильтр'; ?></h2>
            <p><?= isset($selectedSection['DESCRIPTION']) && !empty($selectedSection['DESCRIPTION']) ? $selectedSection['DESCRIPTION'] : 'Выберете необходимые позиции'; ?></p>
        </div>

        <div class="product-detail">
            <h1><?php echo htmlspecialchars($productName); ?></h1>
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($productName); ?>">
            </div>
            <div class="product-description">
                <p><?php echo nl2br(htmlspecialchars($productDescription)); ?></p>
            </div>
            <div class="product-price">
                <p>Цена: <?php echo htmlspecialchars($productPrice); ?></p>
            </div>
            <div class="product-attributes">
                <h2>Характеристики:</h2>
                <ul>
                    <?php foreach ($productProperties as $propName => $propValue): ?>
                        <li><?php echo htmlspecialchars($propName) . ': ' . htmlspecialchars($propValue); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php
    } else {
        echo "Товар не найден.";
    }
}
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
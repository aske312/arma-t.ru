<?php
// get_product_data.php — получение данных товаров по их ID

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!isset($_POST['productIds'])) {
    echo json_encode([]);
    exit;
}

$productIds = json_decode($_POST['productIds'], true); // Декодируем ID товаров
$basketData = [];

if (empty($productIds)) {
    echo json_encode($basketData);
    exit;
}

foreach ($productIds as $productId) {
    // Получаем данные о товаре по ID из инфоблока
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 2, 'ID' => $productId],
        false,
        false,
        ['ID', 'NAME', 'DETAIL_PICTURE', 'CATALOG_PRICE_1']
    );

    if ($arFields = $res->GetNext()) {
        $productImage = CFile::GetPath($arFields['DETAIL_PICTURE']); // Получаем путь к картинке
        $basketData[] = [
            'id' => $arFields['ID'],
            'name' => $arFields['NAME'],
            'price' => $arFields['CATALOG_PRICE_1'] ?? 0, // Если цена есть, иначе 0
            'image' => $productImage,
        ];
    }
}

echo json_encode($basketData);
?>

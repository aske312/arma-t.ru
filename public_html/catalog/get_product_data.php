<?php
// get_product_data.php — получение данных товаров по их ID

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!isset($_POST['productIds'])) {
    echo json_encode([]);
    exit;
}

$productIds = json_decode($_POST['productIds']);
$basketData = [];

foreach ($productIds as $productId) {
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $catalogIblockId, 'ID' => $productId],
        false,
        false,
        ['ID', 'NAME', 'PRICE', 'DETAIL_PICTURE']
    );

    if ($arFields = $res->GetNext()) {
        $productImage = CFile::GetPath($arFields['DETAIL_PICTURE']);
        $basketData[] = [
            'id' => $arFields['ID'],
            'name' => $arFields['NAME'],
            'price' => $arFields['PRICE'] ?? 0, // Цена, если она есть
            'image' => $productImage
        ];
    }
}

echo json_encode($basketData);
?>
<?php
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
        ['ID', 'NAME', 'PRICE']
    );

    if ($arFields = $res->GetNext()) {
        $basketData[] = [
            'id' => $arFields['ID'],
            'name' => $arFields['NAME'],
            'price' => $arFields['PRICE'],
            'quantity' => 1 // По умолчанию или из куки
        ];
    }
}

echo json_encode($basketData);
?>
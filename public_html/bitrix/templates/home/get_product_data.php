<?php
// get_product_data.php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (isset($_POST['id'])) {
    $productId = intval($_POST['id']);
    $arSelect = Array("ID", "NAME", "PROPERTY_PRICE", "PROPERTY_IMAGE");
    $arFilter = Array("IBLOCK_ID" => CATALOG_IBLOCK_ID, "ID" => $productId);
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    if ($ob = $res->GetNext()) {
        $response = [
            'id' => $ob['ID'],
            'name' => $ob['NAME'],
            'price' => $ob['PROPERTY_PRICE_VALUE'] ?: 0,
            'image' => CFile::GetPath($ob['PROPERTY_IMAGE_VALUE']) ?: '/path/to/default/image.jpg'
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
}
?>

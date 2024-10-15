<?php
// Подключение к Bitrix API
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cartItems'])) {
        // Декодируем данные корзины из JSON
        $cartItems = json_decode($_POST['cartItems'], true);

        if (!$cartItems) {
            echo json_encode(["error" => "Invalid product data"]);
            exit;
        }

        $basketData = [];

        foreach ($cartItems as $cartItem) {
            $productId = $cartItem['id'];
            $quantity = $cartItem['quantity'];

            // Запрос к инфоблоку для получения данных товара
            $arSelect = ["ID", "NAME", "EL_PRICE", "DETAIL_PICTURE", "IBLOCK_SECTION_ID"];
            $arFilter = ["IBLOCK_ID" => 2, "ID" => $productId]; // Замените на ваш ID инфоблока
            $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

            if ($arFields = $res->Fetch()) {
                $productImage = $arFields['DETAIL_PICTURE'] ? CFile::GetPath($arFields['DETAIL_PICTURE']) : '/path/to/default/image.jpg';

                $basketData[] = [
                    "id" => $arFields['ID'],
                    "name" => $arFields['NAME'],
                    "price" => $arFields['EL_PRICE']['VALUE'] ?: 0,
                    "quantity" => $quantity,
                    "total" => ($arFields['EL_PRICE']['VALUE'] ?: 0) * $quantity,
                    "picture" => $productImage
                ];
            }
        }

        // Возвращаем данные корзины в формате JSON
        echo json_encode($basketData);
    } else {
        echo json_encode(["error" => "No cart items provided"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>
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

            // Запрос к инфоблоку для получения данных товара, включая пользовательское свойство EL_PRICE
            $arSelect = ['ID', 'NAME', 'PREVIEW_PICTURE', 'PROPERTY_*'] // Получаем цену из пользовательского свойства
            $arFilter = ["IBLOCK_ID" => 2, "ID" => $productId]; // Замените на ваш ID инфоблока
            $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

            if ($arFields = $res->Fetch()) {
                // Обработка изображения товара
                $productImage = $arFields['PREVIEW_PICTURE'] ? CFile::GetPath($arFields['PREVIEW_PICTURE']) : '';

                // Получаем цену из пользовательского свойства EL_PRICE
                $productPrice = $arFields['PROPERTY_EL_PRICE']['VALUE'] ?: 0;

                // Заполняем данные товара
                $basketData[] = [
                    "id" => $arFields['ID'],
                    "name" => $arFields['NAME'],
                    "price" => $productPrice,
                    "quantity" => $quantity,
                    "total" => $productPrice * $quantity,
                    "picture" => $productImage
                ];
            } else {
                // Логируем ошибку, если товар не найден
                error_log("Ошибка: товар с ID $productId не найден.");
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

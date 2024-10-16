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

            // Если цена из куки равна "0", получаем цену из инфоблока
            if ($cartItem['price'] == 0) {
                // Запрос к инфоблоку для получения данных товара
                $arSelect = ["ID", "NAME", "CATALOG_PRICE_1", "DETAIL_PICTURE", "IBLOCK_SECTION_ID"]; // Используем CATATOG_PRICE для цены
                $arFilter = ["IBLOCK_ID" => 2, "ID" => $productId]; // Замените на ваш ID инфоблока
                $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

                if ($arFields = $res->Fetch()) {
                    $productImage = $arFields['DETAIL_PICTURE'] ? CFile::GetPath($arFields['DETAIL_PICTURE']) : ''; //'/path/to/default/image.jpg';
                    $productPrice = $arFields['CATALOG_PRICE_1'] ?: 0; // Получаем цену из каталога

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
            } else {
                // Если цена в куки есть, используем ее
                $basketData[] = [
                    "id" => $cartItem['id'],
                    "name" => $cartItem['name'],
                    "price" => $cartItem['price'],
                    "quantity" => $cartItem['quantity'],
                    "total" => $cartItem['price'] * $cartItem['quantity'],
                    "picture" => '' // Здесь можно добавить путь к картинке, если она у вас хранится
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

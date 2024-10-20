<?php
// Подключение к Bitrix API
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка наличия данных о товарах
    if (isset($_POST['cartItems'])) {
        // Декодируем данные корзины из JSON
        $cartItems = json_decode($_POST['cartItems'], true);

        if (!$cartItems) {
            echo json_encode(["error" => "Invalid product data"]);
            exit;
        }

        $basketData = [];

        foreach ($cartItems as $cartItem) {
            $productId = $cartItem['id']; // Приводим к целому числу для безопасности
            $quantity = $cartItem['quantity'];

            console.log($cartItem);

            // Запрос к инфоблоку для получения данных товара
            $arSelect = ['ID', 'NAME', 'PREVIEW_PICTURE', 'PROPERTY_EL_PRICE']; // Указываем необходимые поля
            $arFilter = ["IBLOCK_ID" => 2, "ID" => $productId]; // Замените на ваш ID инфоблока
            $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

            if ($arFields = $res->Fetch()) {
                // Обработка изображения товара
                //$productImage = $arFields['PREVIEW_PICTURE'] ? CFile::GetPath($arFields['PREVIEW_PICTURE']) : '';
                $productImage = '';

                // Получаем цену из пользовательского свойства EL_PRICE
                //$productPrice = isset($arFields['PROPERTY_EL_PRICE']['VALUE']) ? $arFields['PROPERTY_EL_PRICE']['VALUE'] : 0;
                $productPrice == 0;

                // Заполняем данные товара
                $basketData[] = [
                    "id" => $arFields['ID'],
                    "name" => $arFields['NAME'],
                    "price" => $productPrice, // Приводим к типу float
                    "quantity" => $quantity,
                    "total" => $productPrice * $quantity, // Итоговая стоимость
                    "picture" => $productImage // Путь к изображению товара
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

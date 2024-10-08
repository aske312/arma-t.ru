<?php
// Подключение к Bitrix API
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cart'])) {
        // Попробуем декодировать данные
        $cartItems = json_decode($_POST['cart'], true);
           echo json_encode($cartItems);

        if (!$cartItems) {
            echo json_encode(["error" => "Invalid product data"]);
            exit;
        }

        // Массив для хранения данных товаров
        $basketData = [];

        foreach ($cartItems as $cartItem) {
            $productId = $cartItem['id'];
            $quantity = $cartItem['quantity'];

            // Получаем данные товара из инфоблока по ID
            $arSelect = ["ID", "NAME", "EL_PRICE", "DETAIL_PICTURE", "IBLOCK_SECTION_ID"];
            $arFilter = ["IBLOCK_ID" => 2, "ID" => $productId]; // Замените 2 на ваш ID инфоблока
            $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

            if ($arFields = $res->Fetch()) {
                // Проверяем наличие картинки, если нет — используем картинку раздела
                if ($arFields['DETAIL_PICTURE']) {
                    $pictureSrc = CFile::GetPath($arFields['DETAIL_PICTURE']);
                } else {
                    // Получаем картинку раздела, если нет картинки товара
                    $sectionFilter = ["IBLOCK_ID" => 2, "ID" => $arFields["IBLOCK_SECTION_ID"]];
                    $sectionRes = CIBlockSection::GetList([], $sectionFilter, false, ["PICTURE"]);

                    if ($sectionFields = $sectionRes->Fetch()) {
                        $pictureSrc = CFile::GetPath($sectionFields['PICTURE']);
                    } else {
                        $pictureSrc = '/path/to/default/image.jpg'; // Замените на путь к картинке по умолчанию
                    }
                }

                // Заполняем данные товара для корзины
                $basketData[] = [
                    "id" => $arFields['ID'],
                    "name" => $arFields['NAME'],
                    "price" => $arFields['EL_PRICE']['VALUE'], //?: 0,
                    "quantity" => $quantity,
                    "total" => 0, //($arFields['EL_PRICE']['VALUE'] ?: 0) * $quantity,
                    "picture" => $pictureSrc
                ];
            }
        }

        // Возвращаем данные корзины в JSON формате
        echo json_encode($basketData);
    } else {
        echo json_encode(["error" => "No cart items provided"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>

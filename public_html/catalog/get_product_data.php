<?php
// Подключение к Bitrix API
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем, получены ли данные корзины
    if (isset($_POST['cart'])) {
        // Декодируем данные корзины
        $cartItems = json_decode($_POST['cart'], true);

        // Отладка: выводим полученные куки
        error_log("Полученные куки: " . print_r($_POST['cart'], true));

        if (!$cartItems) {
            echo json_encode(["error" => "Invalid product data"]);
            exit;
        }

        // Массив для данных корзины
        $basketData = [];

        // Обрабатываем каждый товар в корзине
        foreach ($cartItems as $cartItem) {
            $productId = $cartItem['id'];
            $quantity = $cartItem['quantity'];

            // Получаем данные товара по его ID из инфоблока Bitrix
            $arSelect = ["ID", "NAME", "EL_PRICE", "DETAIL_PICTURE", "IBLOCK_SECTION_ID"];
            $arFilter = ["IBLOCK_ID" => 2, "ID" => $productId]; // Замените на ваш ID инфоблока
            $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

            if ($arFields = $res->Fetch()) {
                // Проверка наличия изображения
                if ($arFields['DETAIL_PICTURE']) {
                    $pictureSrc = CFile::GetPath($arFields['DETAIL_PICTURE']);
                } else {
                    // Если нет изображения, используем изображение раздела
                    $sectionFilter = ["IBLOCK_ID" => 2, "ID" => $arFields["IBLOCK_SECTION_ID"]];
                    $sectionRes = CIBlockSection::GetList([], $sectionFilter, false, ["PICTURE"]);

                    if ($sectionFields = $sectionRes->Fetch()) {
                        $pictureSrc = CFile::GetPath($sectionFields['PICTURE']);
                    } else {
                        $pictureSrc = '/path/to/default/image.jpg'; // Установите путь к картинке по умолчанию
                    }
                }

                // Собираем данные товара
                $basketData[] = [
                    "id" => $arFields['ID'],
                    "name" => $arFields['NAME'],
                    "price" => $arFields['EL_PRICE']['VALUE'] ?: 0,
                    "quantity" => $quantity,
                    "total" => ($arFields['EL_PRICE']['VALUE'] ?: 0) * $quantity,
                    "picture" => $pictureSrc
                ];
            }
        }

        // Отправляем данные корзины обратно на клиент
        echo json_encode($basketData);
    } else {
        echo json_encode(["error" => "No cart items provided"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>

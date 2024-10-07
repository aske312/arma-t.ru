<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Подключаемся к Битрикс и получаем данные по ID товара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $productId = intval($_POST['id']);

    // Получаем товар из инфоблока (пример)
    $product = CIBlockElement::GetByID($productId)->GetNext();

    // Если товар найден, получаем данные
    if ($product) {
        $response = [
            'id' => $product['ID'],
            'name' => $product['EL_SH_NAME'],  // Краткое название
            'article' => $product['ARTICLE'],  // Артикул
            'price' => $product['EL_PRICE'] ?? 0,  // Цена
            'picture' => CFile::GetPath($product['EL_PICTURE']),  // Картинка
            'section_picture' => CFile::GetPath($product['SECTION_PICTURE'])  // Картинка раздела
        ];
    } else {
        $response = [
            'error' => 'Товар не найден'
        ];
    }

    echo json_encode($response);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php"); ?>
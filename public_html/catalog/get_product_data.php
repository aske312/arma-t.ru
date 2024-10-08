<?php
// Включение вывода ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

// Проверьте, есть ли данные в POST-запросе
if (!isset($_POST['productIds'])) {
    echo json_encode(['error' => 'No productIds found']);
    exit;
}

$productIds = json_decode($_POST['productIds'], true); // Декодируем данные

// Проверка на успешное декодирование JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'JSON decode error: ' . json_last_error_msg()]);
    exit;
}

// Проверьте, что массив ID не пуст
if (empty($productIds)) {
    echo json_encode(['error' => 'No product IDs provided']);
    exit;
}

$catalogIblockId = 2; // Убедитесь, что здесь правильный ID инфоблока

$basketData = [];

foreach ($productIds as $product) {
    // Проверка на наличие ключей 'id' и 'quantity'
    if (!isset($product['id']) || !isset($product['quantity'])) {
        echo json_encode(['error' => 'Invalid product data']);
        exit;
    }

    $productId = (int)$product['id'];

    // Запрос к инфоблоку Bitrix
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $catalogIblockId, 'ID' => $productId],
        false,
        false,
        ['ID', 'NAME', 'DETAIL_PICTURE', 'EL_PRICE']
    );

    if ($arFields = $res->GetNext()) {
        // Проверка, если есть изображение
        $productImage = $arFields['DETAIL_PICTURE'] ? CFile::GetPath($arFields['DETAIL_PICTURE']) : '/path/to/default_image.jpg'; // Установите изображение по умолчанию
        $basketData[] = [
            'id' => $arFields['ID'],
            'name' => $arFields['NAME'],
            'price' => $arFields['EL_PRICE'] ?? 0, // Цена или 0
            'image' => $productImage,
            'quantity' => (int)$product['quantity'],
        ];
    } else {
        echo json_encode(['error' => "Product with ID $productId not found"]);
        exit;
    }
}

// Вывод результатов
echo json_encode($basketData);
?>

<?php
// Подключение ядра Bitrix
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader;

ob_clean(); // Очистка буфера вывода перед отправкой данных

header('Content-Type: application/json');

// Проверка на подключение модуля инфоблоков
if (!Loader::includeModule('iblock')) {
    echo json_encode(['error' => 'Ошибка загрузки модуля инфоблоков.']);
    exit;
}

// Получаем ID товара из куки (или из POST-запроса)
$productId = isset($_POST['id']) ? (int)$_POST['id'] : (int)$_COOKIE['product_id'];

// Проверяем, что ID товара валиден
if ($productId <= 0) {
    echo json_encode(['error' => 'Некорректный ID товара.']);
    exit;
}

// Запрос данных о товаре из инфоблока (замените на ваш ID инфоблока)
$res = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => 2, 'ID' => $productId],
    false,
    false,
    ['ID', 'NAME', 'DETAIL_PICTURE', 'PROPERTY_PRICE']
);

if ($arFields = $res->Fetch()) {
    // Получение изображения товара или замена на картинку раздела
    $imageSrc = '';
    if ($arFields['DETAIL_PICTURE']) {
        $imageSrc = CFile::GetPath($arFields['DETAIL_PICTURE']);
    } else {
        // Если нет изображения, выводим картинку раздела или картинку по умолчанию
        $sectionRes = CIBlockSection::GetByID($arFields['IBLOCK_SECTION_ID']);
        if ($section = $sectionRes->GetNext()) {
            $imageSrc = CFile::GetPath($section['PICTURE']) ?: '/path/to/default/image.jpg'; // Укажите путь к изображению по умолчанию
        }
    }

    // Если нет цены, устанавливаем 0
    $price = $arFields['PROPERTY_PRICE_VALUE'] ?: 0;

    // Возврат данных в формате JSON
    echo json_encode([
        'id' => $arFields['ID'],
        'name' => $arFields['NAME'],
        'image' => $imageSrc,
        'price' => $price
    ]);
} else {
    echo json_encode(['error' => 'Товар не найден.']);
}
?>
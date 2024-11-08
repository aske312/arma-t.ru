<?php
// Подключение ядра Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader;

$APPLICATION->SetTitle("Поиск");

header('Content-Type: application/json');

// Подключаем модуль инфоблоков
if (!Loader::includeModule("iblock")) {
    echo json_encode([]);
    exit();
}

// Получаем параметр поиска из запроса
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Если запрос пустой, возвращаем пустой массив
if (empty($query)) {
    echo json_encode([]);
    exit();
}

// Фильтруем элементы инфоблока по имени
$filter = [
    'IBLOCK_ID' => 1, // ID вашего инфоблока
    'ACTIVE' => 'Y',
    '%NAME' => $query  // Поиск по имени
];

$res = CIBlockElement::GetList(
    ['NAME' => 'ASC'], // Сортировка по имени
    $filter,
    false,
    ['nTopCount' => 10], // Ограничение на 10 результатов
    ['ID', 'NAME', 'DETAIL_PAGE_URL']  // Выбираем нужные поля
);

$suggestions = [];

while ($ob = $res->GetNext()) {
    $suggestions[] = [
        'name' => $ob['NAME'],
        'url' => $ob['DETAIL_PAGE_URL']  // Ссылка на детальную страницу элемента
    ];
}

// Возвращаем результаты в формате JSON
echo json_encode($suggestions);
?>
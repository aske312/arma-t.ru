<?php
// Подключаем необходимые файлы Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поиск");

header('Content-Type: application/json');  // Устанавливаем тип контента для JSON

// Получаем строку поиска
$query = isset($_GET['q']) ? urldecode(trim($_GET['q'])) : '';

// Если строка поиска пустая или слишком короткая, сразу выходим
if (empty($query) || strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

// Фильтр для поиска по инфоблоку с ID = 1
$elementFilter = [
    'IBLOCK_ID' => 1,
    'ACTIVE' => 'Y',
    '%NAME' => $query,  // Фильтрация по вхождению в поле NAME
];

// Запрос к инфоблоку для получения элементов с учетом фильтра
$res = CIBlockElement::GetList(
    ['NAME' => 'ASC'],  // Сортировка по имени
    $elementFilter,     // Фильтр по имени и статусу
    false,              // Не группируем
    ['nTopCount' => 10], // Ограничиваем 10 результатами
    ['ID', 'NAME']      // Возвращаем только ID и NAME
);

// Собираем результаты
$suggestions = [];
while ($item = $res->Fetch()) {
    $suggestions[] = [
        'id' => $item['ID'],
        'name' => $item['NAME']
    ];
}

// Возвращаем результаты в формате JSON
echo json_encode($suggestions);
?>
<?php
// Проверка на запрос с ошибкой
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

// Подключаем ядро Битрикс для работы с инфоблоками
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Устанавливаем тип контента для JSON
header('Content-Type: application/json');

// Получаем строку поиска
$query = isset($_GET['q']) ? urldecode(trim($_GET['q'])) : '';

// Если строка поиска пустая или слишком короткая, сразу выходим
if (empty($query) || strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

// Фильтр для поиска по инфоблоку с ID = 1
$elementFilter = [
    'IBLOCK_ID' => 1,  // ID инфоблока
    'ACTIVE' => 'Y',    // Только активные элементы
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

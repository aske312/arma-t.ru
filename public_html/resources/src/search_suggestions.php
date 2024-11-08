<?php
// Подключаем ядро Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Включаем вывод ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Получаем строку поиска из GET-параметра 'q'
$query = isset($_GET['q']) ? urldecode(trim($_GET['q'])) : '';

// Если строка поиска пуста, сразу возвращаем пустой массив
if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Фильтр для поиска элементов по имени
$elementFilter = [
    'IBLOCK_ID' => 1,     // ID инфоблока (замените на нужный)
    'ACTIVE' => 'Y',      // Только активные элементы
    '%NAME' => $query,    // Ищем по полю NAME, символ % означает поиск по подстроке
];

// Запрос к инфоблоку для получения элементов
$res = CIBlockElement::GetList(
    ['NAME' => 'ASC'],   // Сортировка по имени в алфавитном порядке
    $elementFilter,      // Фильтр, по которому ищем элементы
    false,               // Без группировки
    ['nTopCount' => 10], // Ограничиваем количество результатов до 10
    ['ID', 'NAME']       // Получаем только ID и NAME для вывода
);

// Массив для хранения предложений
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

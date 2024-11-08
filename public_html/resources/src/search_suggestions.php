<?php
// Подключаем ядро Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Получаем строку поиска из параметра 'q'
$query = isset($_GET['q']) ? urldecode(trim($_GET['q'])) : '';

// Если запрос пустой, возвращаем пустой массив
if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Фильтр для поиска по имени элементов в инфоблоке с ID = 1
$elementFilter = [
    'IBLOCK_ID' => 1,  // ID инфоблока
    'ACTIVE' => 'Y',    // Только активные элементы
    '%NAME' => $query,  // Ищем по полю NAME, с использованием оператора LIKE (символьная подстрока)
];

// Запрос к инфоблоку для получения элементов
$res = CIBlockElement::GetList(
    ['NAME' => 'ASC'],   // Сортировка по имени
    $elementFilter,      // Применение фильтра
    false,               // Без группировки
    ['nTopCount' => 10], // Ограничиваем результат 10 элементами
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

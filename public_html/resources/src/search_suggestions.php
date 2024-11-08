<?php
// Подключаем ядро Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Получаем строку поиска
$query = isset($_GET['q']) ? urldecode(trim($_GET['q'])) : '';

if (empty($query)) {
    echo 'Ошибка: пустой запрос';
    exit;
}

// Фильтр для поиска по инфоблоку с ID = 1
$elementFilter = [
    'IBLOCK_ID' => 1,  // ID инфоблока
    'ACTIVE' => 'Y',    // Только активные элементы
    '%NAME' => $query,  // Фильтрация по вхождению в поле NAME
];

// Проверяем фильтр
echo '<pre>';
print_r($elementFilter);
echo '</pre>';

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

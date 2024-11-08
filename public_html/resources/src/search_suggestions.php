<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json');  // Устанавливаем тип контента для JSON

// Получаем строку запроса от клиента
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Если строка пустая или меньше 3 символов, сразу выходим
if (empty($query) || strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

// Подключаем компоненты Битрикс для поиска по инфоблокам
use Bitrix\Iblock\ElementTable;

// Указываем ID инфоблока, по которому будет производиться поиск
$iblockId = 1;  // ID инфоблока, например, 1 — это ваш инфоблок с названием элементов

// Делаем запрос к БД для поиска элементов по названию (поле NAME)
$res = CIBlockElement::GetList(
    array('NAME' => 'ASC'),  // Сортировка по имени
    array('IBLOCK_ID' => $iblockId, '%NAME' => $query), // Фильтр по имени элемента
    false,  // Не нужно группировать
    array('nTopCount' => 10), // Ограничиваем количество результатов (10)
    array('ID', 'NAME')  // Выбираем ID и NAME (название элемента)
);

$suggestions = [];

// Обрабатываем результаты запроса
while ($item = $res->Fetch()) {
    $suggestions[] = [
        'id' => $item['ID'],
        'name' => $item['NAME']  // Возвращаем только имя элемента
    ];
}

// Возвращаем список подсказок в формате JSON
echo json_encode($suggestions);
?>
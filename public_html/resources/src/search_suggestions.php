<?php
// Подключение ядра Битрикс
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Включаем вывод ошибок для отладки (если в дальнейшем потребуется)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Получаем строку поиска из GET-параметра 'q'
$query = isset($_GET['q']) ? urldecode(trim($_GET['q'])) : '';

// Если строка поиска пуста, сразу возвращаем пустой массив
if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Проверка, подключен ли модуль инфоблоков
if (!CModule::IncludeModule('iblock')) {
    echo json_encode(['error' => 'Модуль инфоблоков не подключен']);
    exit;
}

// Фильтр для поиска элементов по имени
$elementFilter = [
    'IBLOCK_ID' => 1,     // ID инфоблока, замените на нужный
    'ACTIVE' => 'Y',      // Только активные элементы
    '%NAME' => $query,    // Ищем по полю NAME, символ % означает поиск по подстроке
];

// Запрос к инфоблоку для получения элементов
$res = CIBlockElement::GetList(
    ['NAME' => 'ASC'],   // Сортировка по имени в алфавитном порядке
    $elementFilter,      // Фильтр, по которому ищем элементы
    false,               // Без группировки
    ['nTopCount' => 10], // Ограничиваем количество результатов до 10
    ['ID', 'NAME', 'DETAIL_PAGE_URL'] // Получаем ID, NAME и URL для вывода
);

// Массив для хранения предложений
$suggestions = [];
while ($item = $res->Fetch()) {
    $suggestions[] = [
        'id' => $item['ID'],
        'name' => $item['NAME'],
        'url' => $item['DETAIL_PAGE_URL'],  // URL страницы элемента
    ];
}

// Если нашли результаты, формируем HTML для вывода
if (!empty($suggestions)) {
    // Массив HTML-контента для вывода
    $html = '';

    // Перебираем все найденные элементы
    foreach ($suggestions as $suggestion) {
        // Кликабельный блок с ссылкой
        $html .= '<div class="search-result-item">';
        $html .= '<a href="' . $suggestion['url'] . '" class="search-result-link">';
        $html .= htmlspecialchars($suggestion['name']); // Выводим имя элемента
        $html .= '</a>';
        $html .= '</div>';
    }

    // Добавляем кнопку для показа всех результатов
    $html .= '<div class="show-all-results">';
    $html .= '<button onclick="window.location.href=\'/search/?q=' . urlencode($query) . '\'" class="show-all-btn">Показать все результаты</button>';
    $html .= '</div>';

    // Выводим HTML-структуру
    echo json_encode(['html' => $html]);
} else {
    echo json_encode([]);
}
?>

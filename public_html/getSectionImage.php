<?php
// Подключаем ядро Bitrix
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Функция для получения изображения раздела по ID элемента
function getSectionImage($itemId) {
    $catalogIblockId = 5; // Убедитесь, что этот ID соответствует вашему каталогу

    // Получаем элемент по его ID
    $element = CIBlockElement::GetByID($itemId)->GetNextElement();

    if ($element) {
        $elementFields = $element->GetFields();
        $sectionId = $elementFields["IBLOCK_SECTION_ID"]; // ID раздела элемента

        if ($sectionId) {
            // Получаем данные раздела, включая изображение
            $section = CIBlockSection::GetList(
                array(),
                array('ID' => $sectionId, 'IBLOCK_ID' => $catalogIblockId),
                false,
                array('PICTURE')
            )->GetNext();

            if ($section && $section["PICTURE"]) {
                // Получаем URL изображения раздела
                return CFile::GetPath($section["PICTURE"]);
            }
        }
    }

    // Возвращаем изображение по умолчанию, если ничего не найдено
    return '/resources/img/production/0.png';
}

// Проверяем, передан ли itemId через GET-запрос
if (isset($_GET['itemId']) && !empty($_GET['itemId'])) {
    $itemId = intval($_GET['itemId']); // Преобразуем в целое число для безопасности
    $imageUrl = getSectionImage($itemId);

    // Возвращаем результат в формате JSON
    echo json_encode(['imageUrl' => $imageUrl]);
} else {
    // Если itemId не передан, возвращаем ошибку
    http_response_code(400);
    echo json_encode(['error' => 'itemId не передан']);
}

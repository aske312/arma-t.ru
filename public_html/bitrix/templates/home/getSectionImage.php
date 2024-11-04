<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json');

$itemId = $_GET['itemId'];
$imageUrl = '/resources/img/production/0.png'; // Изображение по умолчанию

if ($itemId) {
    $catalogIblockId = 1; // Укажите ID инфоблока "catalog"

    // Получаем элемент по его ID
    $element = CIBlockElement::GetByID($itemId)->GetNextElement();

    // Проверка, что элемент найден
    if ($element) {
        $elementFields = $element->GetFields();
        $sectionId = $elementFields["IBLOCK_SECTION_ID"];

        // Проверка, что ID раздела существует
        if ($sectionId) {
            $section = CIBlockSection::GetByID($sectionId)->GetNext();

            // Проверка, что раздел найден и у него есть изображение
            if ($section && $section["PICTURE"]) {
                $imageUrl = CFile::GetPath($section["PICTURE"]);
            }
        }
    }
}

// Возвращаем URL изображения раздела в формате JSON
echo json_encode(['imageUrl' => $imageUrl]);
?>

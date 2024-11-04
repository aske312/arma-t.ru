<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php"); // Подключение Bitrix API

function getSectionImage($itemId) {
    $catalogIblockId = 'catalog';

    $element = CIBlockElement::GetByID($itemId)->GetNextElement();

    if ($element) {
        $elementFields = $element->GetFields();
        $sectionId = $elementFields["IBLOCK_SECTION_ID"];

        if ($sectionId) {
            $section = CIBlockSection::GetList(
                array(),
                array('ID' => $sectionId, 'IBLOCK_ID' => $catalogIblockId),
                false,
                array('PICTURE')
            )->GetNext();

            if ($section && $section["PICTURE"]) {
                $sectionImage = CFile::GetPath($section["PICTURE"]);
                return $sectionImage;
            }
        }
    }

    return '/resources/img/production/0.png';
}

// Получаем itemId из параметров запроса
$itemId = $_GET['itemId'];

// Получаем изображение раздела
$imageUrl = getSectionImage($itemId);

// Возвращаем результат в формате JSON
echo json_encode(['imageUrl' => $imageUrl]);

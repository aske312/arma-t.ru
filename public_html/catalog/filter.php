<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$sectionId = intval($_POST['SECTION_ID'] ?? 1);
$filterProperties = ['EL_CONNTYPE', 'EL_DRIVETYPE', 'EL_DN_DIAMETER_MM', 'EL_PN_PRESSURE_KGF_CM2', 'EL_BODY_MATERIAL'];
$elementFilter = [
    'IBLOCK_ID' => 1,
    'SECTION_ID' => $sectionId,
    'ACTIVE' => 'Y',
];

foreach ($filterProperties as $propertyCode) {
    if (!empty($_POST[$propertyCode]) && $_POST[$propertyCode] !== 'all') {
        $elementFilter['PROPERTY_' . $propertyCode] = $_POST[$propertyCode];
    }
}

$res = CIBlockElement::GetList(
    ['ID' => 'ASC'],
    $elementFilter,
    false,
    ['nPageSize' => 10],
    ['ID', 'NAME', 'PROPERTY_EL_PRICE']
);

$items = [];
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();
    $items[] = [
        'name' => $arFields['NAME'],
        'price' => $arProps['EL_PRICE']['VALUE'],
    ];
}

echo json_encode($items);
?>

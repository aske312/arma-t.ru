<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>

<?php
if (CModule::IncludeModule("iblock")) {
    $productId = intval($_GET['id']);
    $res = CIBlockElement::GetByID($productId);

    if ($ar_res = $res->GetNext()) {
        $productName = $ar_res['NAME'];
        $productDescription = $ar_res['DETAIL_TEXT'];
        // Здесь можно получить цену и другие свойства
        echo "<h1>$productName</h1>";
        echo "<div>$productDescription</div>";

        $properties = CIBlockElement::GetProperty($ar_res['IBLOCK_ID'], $productId, array("sort" => "asc"), array());
        while ($prop = $properties->Fetch()) {
            echo "<div>{$prop['NAME']}: {$prop['VALUE']}</div>";
        }
    } else {
        echo "Товар не найден.";
    }
}
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
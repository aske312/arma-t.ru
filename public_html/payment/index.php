<?php
// Подключаем файл с настройками
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/local/css/delivery.css"); //css

// Указываем ID инфоблока
$IBLOCK_ID = 1; // Замените **1** на ID вашего инфоблока

// Получаем данные из инфоблока
$arSelect = Array("ID", "NAME", "PROPERTY_*");
$arFilter = Array("IBLOCK_ID" => $IBLOCK_ID, "ACTIVE" => "Y");
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect); ?>

<div class="section-title">
    <h2><?php echo htmlspecialchars($productName); ?></h2> <!-- Название раздела -->
    <p>Подробное описание</p>
</div>

<?php
    // Выводим информацию на страницу
    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();

        echo "<h2>{$arFields['NAME']}</h2>";
        echo "<p>{$arProps['TEXT']['VALUE']}</p>"; // Замените 'TEXT' на название свойства с текстом
}?>

<button class="back-button" onclick="history.back()">Назад</button> <!-- Кнопка Назад -->
<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
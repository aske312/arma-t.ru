<?php
// Подключаем файл с настройками
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/local/css/delivery.css"); //css

// Указываем ID инфоблока
$IBLOCK_ID = 1; ?>

<div class="section-title">
    <h2><?php echo htmlspecialchars($productName); ?></h2> <!-- Название раздела -->
    <p>Подробное описание</p>
</div>

<button class="back-button" onclick="history.back()">Назад</button> <!-- Кнопка Назад -->


<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
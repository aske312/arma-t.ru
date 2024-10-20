<?php
// Подключаем файл с настройками
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/local/css/delivery.css"); //css

// Указываем ID инфоблока
$IBLOCK_ID = 1; ?>

<div class="section-title">
    <h2> Доставка </h2> <!-- Название раздела -->
    <p> Подробная информация по доставке </p>
</div>

<div class="product-delivery">
    <button class="back-button" onclick="history.back()">Назад</button> <!-- Кнопка Назад -->
</div>

<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
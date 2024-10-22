<?php
// Подключаем файл с настройками
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/payment.css"); //css

// Указываем ID инфоблока
$IBLOCK_ID = 1; ?>

<div class="section-title">
    <h2> Оплата </h2> <!-- Название раздела -->
    <p> Подробная информация по оплате </p>
</div>

<div class="product-payments">
    <div>
        Вы можете оплатить Ваш заказ по безналичному расчету, через любой банк РФ.
        Для выставления счета от Вас потребуется - карточка организации с банковскими реквизитами, телефон, адрес доставки, а также электронный адрес.
        Деньги поступают на расчетный счет на следующий день после оплаты.
        Статус поступления средств Вы можете уточнить у нашего менеджера.
        Наши реквизиты:
    </div>

    <button class="back-button" onclick="history.back()">Назад</button> <!-- Кнопка Назад -->
</div>

<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php'); ?>
<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// MOBILE VERSION
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $userAgent);

if ($isMobile) {
    include 'footer_mobile.php';
    return;
}
?>

<div class="section footer" id="contact">
    <div class="conts-wrapper">
        <!-- Контактная информация -->
        <div class="cont-info">
            <h2>Контакты</h2>
            <div class="cont-details">
                <?php
                // Получаем элементы инфоблока с контактной информацией (раздел 35)
                $contactsFilter = [
                    'IBLOCK_ID' => 4, // ID инфоблока
                    //'SECTION_ID' => 4, // ID раздела 35
                    'ACTIVE' => 'Y' // Активные элементы
                ];

                // Делаем выборку: ID элемента, Название и Текст описания (EL_DESCRIPTION)
                $contactsSelect = ['ID', 'NAME', 'PROPERTY_EL_DESCRIPTION']; // Используем свойство EL_DESCRIPTION
                $contactsRes = CIBlockElement::GetList([], $contactsFilter, false, false, $contactsSelect);

                while ($contact = $contactsRes->GetNext()) {
                    // Получаем текст описания, если есть
                    $descriptionText = !empty($contact['PROPERTY_EL_DESCRIPTION_VALUE']) ? $contact['PROPERTY_EL_DESCRIPTION_VALUE'] : 'Не указано';
                    echo "<p><strong>" . $contact['NAME'] . ":</strong> " . $descriptionText . "</p>";
                }
                ?>

                <h3>Наши реквизиты:</h3>

                <?php
                // Получаем элементы инфоблока с реквизитами (раздел 36)
                $requisitesFilter = [
                    'IBLOCK_ID' => 3, // ID инфоблока
                    //'SECTION_ID' => 3, // ID раздела 36
                    'ACTIVE' => 'Y' // Активные элементы
                ];

                // Делаем выборку: ID элемента, Название и Текст описания (EL_DESCRIPTION)
                $requisitesSelect = ['ID', 'NAME', 'PROPERTY_EL_DESCRIPTION']; // Используем свойство EL_DESCRIPTION
                $requisitesRes = CIBlockElement::GetList([], $requisitesFilter, false, false, $requisitesSelect);

                while ($requisite = $requisitesRes->GetNext()) {
                    // Получаем текст описания, если есть
                    $descriptionText = !empty($requisite['PROPERTY_EL_DESCRIPTION_VALUE']) ? $requisite['PROPERTY_EL_DESCRIPTION_VALUE'] : 'Не указано';
                    echo "<p><strong>" . $requisite['NAME'] . ":</strong> " . $descriptionText . "</p>";
                }
                ?>
            </div>
        </div>

        <!-- Блок с картой -->
        <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
        <div class="map">
            <div id="map" style="width: 475px; height: 475px;"></div>
            <script>
                ymaps.ready(init);
                function init() {
                    var myMap = new ymaps.Map("map", {
                        center: [55.681717, 37.269466], // Координаты центра карты
                        zoom: 15 // Уровень масштабирования
                    });

                    // Добавление метки
                    var myPlacemark = new ymaps.Placemark([55.681717, 37.269466], {
                        balloonContent: 'Улица Маршала Жукова, 36'
                    });

                    myMap.geoObjects.add(myPlacemark);
                }
            </script>
        </div>
    </div>
</div>

<!-- Политика -->

<div class="footer-title">
    <div class="developer-credit">
        <div class="footer-left">
            <h1>
                © 2025 ООО «АРМА-Т»
            </h1>
            <h1>
                <a href="/policy/">Политика конфиденциальности</a>
            </h1>
            <p>
                Информация на сайте не является публичной офертой,
                носит рекламный характер и расценивается как приглашение
                делать оферты на основании п.1 ст. 437 ГК РФ.
                Для получения актуальной стоимости продукции присылайте нам запрос.
                Копирование любой информации с данного ресурса без согласия его владельца
                влечет за собой ответственность согласно статье 272 УК РФ
            </p>
        </div>
    </div>
</div>

</body>
</html>

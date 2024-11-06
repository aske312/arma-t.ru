<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="section footer" id="contact">
    <div class="conts-wrapper">
        <!-- Контактная информация -->
        <div class="cont-info">
            <h2>Контакты</h2>
            <div class="cont-details">
                <?php
                // Получаем элементы инфоблока с контактной информацией (раздел 35)
                $contactsFilter = [
                    'IBLOCK_ID' => 3, // ID инфоблока
                    'SECTION_ID' => 35, // ID раздела 35
                    'ACTIVE' => 'Y'
                ];

                $contactsSelect = ['ID', 'NAME', 'PROPERTY_ANNOUNCE', 'PROPERTY_TEXT'];
                $contactsRes = CIBlockElement::GetList([], $contactsFilter, false, false, $contactsSelect);

                while ($contact = $contactsRes->GetNext()) {
                    // Выводим название и текст анонса каждого элемента
                    echo "<p>" . $contact['NAME'] . ": " . $contact['PROPERTY_TEXT'] . "</p>";
                }
                ?>

                <h3>Наши реквизиты:</h3>

                <?php
                // Получаем элементы инфоблока с реквизитами (раздел 36)
                $requisitesFilter = [
                    'IBLOCK_ID' => 3, // ID инфоблока
                    'SECTION_ID' => 36, // ID раздела 36
                    'ACTIVE' => 'Y'
                ];

                $requisitesSelect = ['ID', 'NAME', 'PROPERTY_ANNOUNCE', 'PROPERTY_TEXT'];
                $requisitesRes = CIBlockElement::GetList([], $requisitesFilter, false, false, $requisitesSelect);

                while ($requisite = $requisitesRes->GetNext()) {
                    // Выводим название и текст анонса каждого элемента
                    echo "<p>" . $requisite['NAME'] . ": " . $requisite['PROPERTY_TEXT'] . "</p>";
                }
                ?>
            </div>
        </div>

        <!-- Блок с картой -->
        <div class="map">
            <iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3A91f7b8e166afeeae94308f6df98ec9af515a64477bc6ed62f27b0ed0b78e66f8&source=constructor&point=55.681717,37.269466" width="475" height="475" frameborder="0"></iframe>
        </div>
    </div>
</div>

<!-- Подпись о разработке -->
<div class="section footer-title">
    <div class="developer-credit">
        <p>Сайт разработан ХХХХХХХХХХ</p>
    </div>
</div>

</body>
</html>

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
        <div class="map">
            <iframe
                src="https://yandex.ru/map-widget/v1/?um=constructor%3A5a2d5d5e7c8c2603e2d8a2b9e5c9d7e78b0b0c824e3f1c5e44f21b61a3e1e5a5&source=constructor&point=55.681717,37.269466"
                width="475"
                height="475"
                frameborder="0">
            </iframe>
        </div>
    </div>
</div>

<!-- Подпись о разработке -->
<div class="section footer-title">
    <div class="developer-credit">
        <!-- <p>Сайт разработан ХХХХХХХХХХ</p> -->
    </div>
</div>

</body>
</html>

<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<div class="footer-m" id="contact">
    <div class="conts-wrapper-m">
        <!-- Контактная информация -->
        <div class="cont-info-m">
            <h2>Контакты</h2>
            <div class="cont-details-m">
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
        <div class="map-m">
            <iframe src="https://yandex.ru/map-widget/v1/?z=12&ol=biz&oid=168551027459" width="200" height="325" frameborder="0"></iframe>
        </div>
    </div>
</div>

<!-- Политика -->

<div class="footer-title-m">
    <div class="developer-credit-m">
        <div class="footer-left-m">
            <h1>
                © 2025 ООО «АРМА-Т»
            </h1>
            <h1>
                <a href="/policy/">политика конфиденциальности</a>
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

<? if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
    </div>
    <div class="section footer" id="contact">
        <div class="conts-wrapper">
            <div class="cont-info">
                <h2>Контакты</h2>

                <div class="cont-details">
                    <?
                    // Получаем контактную информацию
                    $arSelect = Array("ID", "NAME", "PROPERTY_PHONE", "PROPERTY_EMAIL", "PROPERTY_ADDRESS");
                    $arFilter = Array("IBLOCK_ID"=>1, "ACTIVE"=>"Y", "SECTION_ID"=>1); // 1 - ID раздела Контактная информация
                    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                    while($ob = $res->GetNextElement()):
                        $arFields = $ob->GetFields();
                    ?>
                        <p>Тел.: <?= $arFields["PROPERTY_PHONE_VALUE"] ?></p>
                        <p>Почта: <?= $arFields["PROPERTY_EMAIL_VALUE"] ?></p>
                        <p>Адрес: <?= $arFields["PROPERTY_ADDRESS_VALUE"] ?></p>
                    <? endwhile; ?>

                    <h3>Наши реквизиты:</h3>
                    <?
                    // Получаем реквизиты
                    $arSelect = Array("ID", "NAME", "PROPERTY_INN", "PROPERTY_OGRN", "PROPERTY_KPP");
                    $arFilter = Array("IBLOCK_ID"=>1, "ACTIVE"=>"Y", "SECTION_ID"=>2); // 2 - ID раздела Реквизиты
                    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                    while($ob = $res->GetNextElement()):
                        $arFields = $ob->GetFields();
                    ?>
                        <p>ИНН: <?= $arFields["PROPERTY_INN_VALUE"] ?></p>
                        <p>ОГРН: <?= $arFields["PROPERTY_OGRN_VALUE"] ?></p>
                        <p>КПП: <?= $arFields["PROPERTY_KPP_VALUE"] ?></p>
                    <? endwhile; ?>
                </div>

                <!--
                <div class="cont-details">
                    <p>Тел.: +7 (000) 000-00-00</p>
                    <p>Почта: support@arma-t.ru</p>
                    <p>улица Маршала Жукова, 36, г. Москва</p>
                    <p>ул. ХХХХХХХХХХ, 00</p>
                    <h3>Наши реквизиты:</h3>
                    <p>ИНН: 00000000000000</p>
                    <p>ОГРН: 0000000000000</p>
                    <p>КПП: 00000000000000</p>
                </div>
                -->

            </div>
            <div class="map">
                <iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3A91f7b8e166afeeae94308f6df98ec9af515a64477bc6ed62f27b0ed0b78e66f8&source=constructor&point=37.269466, 55.681717" width="475" height="475" frameborder="0"></iframe>
            </div>
        </div>
    </div>
    <div class="section footer-title">
        <div class="developer-credit">
            <p>Сайт разработан ХХХХХХХХХХ</p>
        </div>
    </div>
<!-- -->

	</body>
</html>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Футер");

// Массив для хранения информации из раздела 35 (Контактная информация)
$contacts = [];
// Массив для хранения информации из раздела 36 (Реквизиты)
$requisites = [];

// Получаем элементы из инфоблока для контактной информации (Раздел 35)
$contactFilter = [
    'IBLOCK_ID' => 3, // ID вашего инфоблока для контактной информации
    'SECTION_ID' => 35, // Раздел для контактной информации
    'ACTIVE' => 'Y'
];

$contactSelect = ['ID', 'NAME', 'PREVIEW_TEXT']; // Название и анонс текста
$resContacts = CIBlockElement::GetList([], $contactFilter, false, false, $contactSelect);
while ($contact = $resContacts->Fetch()) {
    $contacts[] = $contact;
}

// Получаем элементы из инфоблока для реквизитов (Раздел 36)
$requisiteFilter = [
    'IBLOCK_ID' => 3, // ID вашего инфоблока для реквизитов
    'SECTION_ID' => 36, // Раздел для реквизитов
    'ACTIVE' => 'Y'
];

$requisiteSelect = ['ID', 'NAME', 'PREVIEW_TEXT']; // Название и анонс текста
$resRequisites = CIBlockElement::GetList([], $requisiteFilter, false, false, $requisiteSelect);
while ($requisite = $resRequisites->Fetch()) {
    $requisites[] = $requisite;
}
?>

<!-- Футер -->
<footer>
    <div class="footer-container">
        <!-- Контактная информация -->
        <div class="footer-contact-info">
            <h3>Контактная информация</h3>
            <?php if (!empty($contacts)): ?>
                <ul>
                    <?php foreach ($contacts as $contact): ?>
                        <li>
                            <strong><?= htmlspecialchars($contact['NAME']); ?></strong><br>
                            <span><?= htmlspecialchars($contact['PREVIEW_TEXT']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Контактная информация не найдена.</p>
            <?php endif; ?>
        </div>

        <!-- Реквизиты -->
        <div class="footer-requisites">
            <h3>Реквизиты</h3>
            <?php if (!empty($requisites)): ?>
                <ul>
                    <?php foreach ($requisites as $requisite): ?>
                        <li>
                            <strong><?= htmlspecialchars($requisite['NAME']); ?></strong><br>
                            <span><?= htmlspecialchars($requisite['PREVIEW_TEXT']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Реквизиты не найдены.</p>
            <?php endif; ?>
        </div>

        <!-- Карта с точкой -->
        <div class="footer-map">
            <h3>Наша локация</h3>
            <div id="map" style="width: 100%; height: 400px;"></div>
        </div>
    </div>
</footer>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=ВАШ_API_КЛЮЧ" type="text/javascript"></script>
<script>
    ymaps.ready(function () {
        var map = new ymaps.Map("map", {
            center: [55.681717, 37.269466], // Координаты точки
            zoom: 14
        });

        var placemark = new ymaps.Placemark([55.681717, 37.269466], {
            balloonContent: 'Наш офис'
        });

        map.geoObjects.add(placemark);
    });
</script>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>

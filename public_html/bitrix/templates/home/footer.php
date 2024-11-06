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
<div class="footer">
    <div class="conts-wrapper">
        <!-- Контактная информация -->
        <div class="cont-info">
            <h2>Контактная информация</h2>
            <?php if (!empty($contacts)): ?>
                <div class="cont-details">
                    <?php foreach ($contacts as $contact): ?>
                        <p><strong><?= htmlspecialchars($contact['NAME']); ?>:</strong> <?= htmlspecialchars($contact['PREVIEW_TEXT']); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Контактная информация не найдена.</p>
            <?php endif; ?>

            <!-- Реквизиты -->
            <h2>Реквизиты</h2>
            <?php if (!empty($requisites)): ?>
                <div class="cont-details">
                    <?php foreach ($requisites as $requisite): ?>
                        <p><strong><?= htmlspecialchars($requisite['NAME']); ?>:</strong> <?= htmlspecialchars($requisite['PREVIEW_TEXT']); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Реквизиты не найдены.</p>
            <?php endif; ?>
        </div>

        <!-- Карта -->
        <div class="map">
            <h2>Наши координаты</h2>
            <div class="map-container">
                <div class="map-point"></div>
            </div>
        </div>
    </div>

    <!-- Сайт разработан -->
    <div class="developer-credit">
        <p>Сайт разработан <strong>Название компании</strong></p>
    </div>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
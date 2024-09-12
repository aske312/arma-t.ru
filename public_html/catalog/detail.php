<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// Подключение модуля инфоблоков
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Page\Asset;

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/header.css");  //css
Asset::getInstance()->addCss("/local/css/catalog.css"); //css
Asset::getInstance()->addCss("/local/css/footer.css");  //css
Asset::getInstance()->addJs("/local/js/script.js");     // js

$sectionId = intval($_GET['SECTION_ID']);

Loader::includeModule('iblock');

?>

<!-- YA_DETAIL_PHP -->

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
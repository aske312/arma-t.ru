<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/policy.css");

// MOBILE VERSION
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|Windows Phone|Opera Mini|IEMobile/i', $userAgent);

if ($isMobile) {
    include 'index_mobile.php';
    return;
}
?>

<div class="policy-title">
    <h2> Политики использования </h2>
    <p> Подробности </p>
</div>

<div></div>

<script>
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

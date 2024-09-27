<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start();
$productId = intval($_POST['id']);
if (isset($_SESSION['CART'][$productId])) {
    unset($_SESSION['CART'][$productId]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Товар не найден в корзине']);
}
exit;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>

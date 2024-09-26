<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start();
$productId = (int) $_POST['id'];

if (isset($_SESSION['CART'][$productId])) {
    unset($_SESSION['CART'][$productId]);
    echo json_encode(['success' => 'Товар удален из корзины']);
} else {
    echo json_encode(['error' => 'Товар не найден в корзине']);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>

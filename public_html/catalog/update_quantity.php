<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start();

$productId = intval($_POST['id']);
$quantity = intval($_POST['quantity']);

if (isset($_SESSION['CART'][$productId])) {
    $_SESSION['CART'][$productId]['quantity'] = $quantity;

    // Возвращаем обновленные данные корзины
    echo json_encode(array_values($_SESSION['CART']));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>

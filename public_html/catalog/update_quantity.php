<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start();

$request = Context::getCurrent()->getRequest();
$productId = intval($request->getPost('id'));
$quantity = intval($request->getPost('quantity'));

if (isset($_SESSION['BASKET'][$productId])) {
    $_SESSION['BASKET'][$productId]['quantity'] = $quantity;
    $_SESSION['BASKET'][$productId]['total'] = $_SESSION['BASKET'][$productId]['price'] * $quantity;
}

// Возвращаем обновленные данные корзины
echo json_encode(array_values($_SESSION['BASKET']));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>

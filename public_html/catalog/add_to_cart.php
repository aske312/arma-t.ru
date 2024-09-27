<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context;

session_start();

$request = Context::getCurrent()->getRequest();
$productId = intval($request->getPost('id'));
$productArticle = $request->getPost('article');
$productPrice = floatval($request->getPost('price'));

// Проверяем, есть ли такой товар в корзине
if (!isset($_SESSION['BASKET'][$productId])) {
    $_SESSION['BASKET'][$productId] = [
        'id' => $productId,
        'name' => $productId, // Вы можете также добавить название, если оно нужно
        'article' => $productArticle,
        'price' => $productPrice,
        'quantity' => 1,
        'total' => $productPrice,
    ];
} else {
    $_SESSION['BASKET'][$productId]['quantity']++;
    $_SESSION['BASKET'][$productId]['total'] = $_SESSION['BASKET'][$productId]['quantity'] * $productPrice;
}

// Возвращаем обновленные данные корзины
echo json_encode(array_values($_SESSION['BASKET']));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
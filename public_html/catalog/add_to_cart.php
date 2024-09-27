<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context;
use Bitrix\Main\Loader;

session_start();
Loader::includeModule('iblock');

$request = Context::getCurrent()->getRequest();
$productId = intval($request->getPost('id'));

// Получаем информацию о товаре
$arSelect = ["ID", "NAME", "PRICE"];
$arFilter = ["IBLOCK_ID" => 2, "ID" => $productId];
$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

if ($item = $res->Fetch()) {
    if (!isset($_SESSION['BASKET'][$productId])) {
        $_SESSION['BASKET'][$productId] = [
            'name' => $item['NAME'],
            'price' => $item['PRICE'],
            'quantity' => 1,
            'total' => $item['PRICE'],
        ];
    } else {
        $_SESSION['BASKET'][$productId]['quantity']++;
        $_SESSION['BASKET'][$productId]['total'] = $_SESSION['BASKET'][$productId]['quantity'] * $item['PRICE'];
    }
}

// Возвращаем обновленные данные корзины
echo json_encode(array_values($_SESSION['BASKET']));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
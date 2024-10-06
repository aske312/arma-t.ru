<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//use Bitrix\Main\Context;

//session_start();
//
//$request = Context::getCurrent()->getRequest();
//$productId = intval($request->getPost('id'));
//$productArticle = $request->getPost('article');
//$productPrice = floatval($request->getPost('price'));

//// Проверяем, есть ли такой товар в корзине
//if (!isset($_SESSION['BASKET'][$productId])) {
//    $_SESSION['BASKET'][$productId] = [
//        'id' => $productId,
//        'name' => $productId, // Вы можете также добавить название, если оно нужно
//        'article' => $productArticle,
//        'price' => $productPrice,
//        'quantity' => 1,
//        'total' => $productPrice,
//    ];
//} else {
//    $_SESSION['BASKET'][$productId]['quantity']++;
//    $_SESSION['BASKET'][$productId]['total'] = $_SESSION['BASKET'][$productId]['quantity'] * $productPrice;
//}

use Bitrix\Main\Loader;
Loader::includeModule("iblock");

// Получаем ID товара из POST-запроса
$productId = intval($_POST['id']);

// Проверяем, что ID валидный
if ($productId > 0) {
    // Получаем товар из инфоблока "catalog"
    $arSelect = ["ID", "NAME", "DETAIL_PICTURE", "CATALOG_PRICE_1"];
    $arFilter = ["IBLOCK_ID" => 2, "ID" => $productId]; // Замените IBLOCK_ID на ID вашего инфоблока
    $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

    if ($arItem = $res->GetNext()) {
        // Получаем изображение
        $pictureSrc = !empty($arItem["DETAIL_PICTURE"]) ? CFile::GetPath($arItem["DETAIL_PICTURE"]) : '/path/to/default/picture.jpg';

        // Формируем данные товара
        $item = [
            'id' => $arItem["ID"],
            'name' => $arItem["NAME"],
            'picture' => $pictureSrc,
            'price' => $arItem["CATALOG_PRICE_1"],
            'quantity' => 1, // по умолчанию количество 1
        ];

        // Добавляем товар в сессию (или базу данных)
        $_SESSION['CART'][$arItem["ID"]] = $item;

        // Возвращаем данные корзины
        echo json_encode(array_values($_SESSION['CART']));
    }
}

// Возвращаем обновленные данные корзины
echo json_encode(array_values($_SESSION['BASKET']));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
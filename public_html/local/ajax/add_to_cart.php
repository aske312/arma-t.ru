<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start(); // Открываем сессию

// Получение данных из POST-запроса
$productId = (int) $_POST['id'];
$articul = htmlspecialchars($_POST['articul']);
$price = (float) $_POST['price'];

// Инициализация корзины в сессии, если ее еще нет
if (!isset($_SESSION['CART'])) {
    $_SESSION['CART'] = [];
}

// Проверка, есть ли товар уже в корзине
if (!isset($_SESSION['CART'][$productId])) {
    // Добавляем товар в корзину
    $_SESSION['CART'][$productId] = [
        'ARTICUL' => $articul,
        'PRICE' => $price,
        'QUANTITY' => 1
    ];
    echo json_encode(['success' => 'Товар добавлен в корзину']);
} else {
    // Увеличиваем количество товара в корзине
    $_SESSION['CART'][$productId]['QUANTITY']++;
    echo json_encode(['success' => 'Количество товара увеличено']);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>

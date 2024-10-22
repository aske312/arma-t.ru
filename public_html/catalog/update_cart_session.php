<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start(); // Запуск сессии

// Получаем данные из POST-запроса
$postData = json_decode(file_get_contents('php://input'), true);

if (isset($postData['cartItems'])) {
    $_SESSION['cartItems']['cartItems'] = $postData['cartItems']; // Сохраняем товары в сессию
    echo json_encode(['status' => 'success']); // Возвращаем успех
} else {
    echo json_encode(['status' => 'error', 'message' => 'No cart items found']);
}

?>
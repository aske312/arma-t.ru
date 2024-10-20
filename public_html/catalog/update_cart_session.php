<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start(); // Не забудьте запустить сессию

// Получаем данные из запроса
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['cartItems'])) {
    $_SESSION['cartItems'] = $data['cartItems']; // Сохраняем товары в сессии
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No cart items provided']);
}


?>
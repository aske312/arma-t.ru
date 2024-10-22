<?php
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные корзины из POST запроса
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['cartItems'])) {
        // Обновляем корзину в сессии
        $_SESSION['cartItems'] = $data['cartItems'];

        // Возвращаем успешный ответ
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No cart items found']);
    }
}

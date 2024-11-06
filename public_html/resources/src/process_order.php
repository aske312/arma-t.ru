<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Получаем данные из POST-запроса
$orderData = json_decode(file_get_contents("php://input"), true);

if ($orderData && isset($orderData['cartItems'])) {
    // Здесь логика обработки заказа: создание заказа, сохранение в базе данных и отправка уведомления

    // Например, код для сохранения заказа в сессии:
    $_SESSION['lastOrder'] = $orderData;

    // Возвращаем JSON-ответ
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
}
?>
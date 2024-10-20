<?php
session_start(); // Запуск сессии

// Получаем данные из POST-запроса
$data = json_decode(file_get_contents('php://input'), true);

// Сохраняем данные корзины в сессию
if (isset($data['cartItems'])) {
    $_SESSION['cartItems'] = $data['cartItems'];
}

// Возвращаем успешный ответ
http_response_code(200);
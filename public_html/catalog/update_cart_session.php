<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartItems'])) {
    $cartItems = $_POST['cartItems'];

    // Сохраняем в сессии
    $_SESSION['cartItems'] = $cartItems;

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>
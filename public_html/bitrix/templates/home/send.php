<?php
// Включаем обработку ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Убедимся, что данные были переданы через POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Получаем данные из формы
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $subject = htmlspecialchars(trim($_POST["subject"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // Проверка на обязательные поля
    if (empty($name) || empty($email) || empty($subject)) {
        echo json_encode(['status' => 'error', 'message' => 'Пожалуйста, заполните все обязательные поля.']);
        exit;
    }

    // Письмо
    $to = "support@arma-t.ru"; // Замените на свой адрес
    $headers = "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $name <$email>\r\n";

    // Составляем сообщение
    $body = "<h2>Новая заявка с сайта</h2>";
    $body .= "<p><strong>Имя:</strong> $name</p>";
    $body .= "<p><strong>Email:</strong> $email</p>";
    $body .= "<p><strong>Компания:</strong> $subject</p>";
    $body .= "<p><strong>Комментарий:</strong> $message</p>";

    // Отправляем письмо
    if (mail($to, "Заявка с сайта: $subject", $body, $headers)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при отправке заявки.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный запрос.']);
}
?>
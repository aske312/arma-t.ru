<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $subject = trim($_POST["subject"]);
    $message = trim($_POST["message"]);

    // Валидация полей
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Заполните все поля']);
        exit;
    }

    // Настройки почты
    $to = 'support@arma-t.ru';  // Здесь ваш email на хостинге sweb.ru
    $headers = "From: no-reply@your-domain.ru\r\n" .
               "Reply-To: $email\r\n" .
               "Content-Type: text/plain; charset=utf-8\r\n";
    $subject_mail = "Заявка с сайта: $subject";
    $body = "Имя: $name\nE-mail: $email\nКомпания: $subject\nСообщение: $message";

    // Отправка почты
    if (mail($to, $subject_mail, $body, $headers)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка при отправке сообщения']);
    }
}
?>
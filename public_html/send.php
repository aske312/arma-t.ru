<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Заголовки для отправки письма с кодировкой UTF-8
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $to = "support@arma-t.ru";  // Укажите ваш email
    $subject = "Новая заявка с формы";
    $body = "
    <html>
    <body>
        <h2>Заявка с формы обратной связи</h2>
        <p><strong>Имя:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Компания:</strong> $subject</p>
        <p><strong>Сообщение:</strong> $message</p>
    </body>
    </html>";

    if (mail($to, $subject, $body, $headers)) {
        http_response_code(200);  // Сообщение об успешной отправке
    } else {
        http_response_code(500);  // Сообщение об ошибке
    }
}
?>
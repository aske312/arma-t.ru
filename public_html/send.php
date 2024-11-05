<?php
// Ключ из Google reCAPTCHA
$secret = '6Lca1HUqAAAAANhTUDGv7N3sp7RdabodoCoHCNrX';

// Проверка, была ли отправлена капча
if(isset($_POST['g-recaptcha-response'])) {
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $recaptchaResponse;
    $response = file_get_contents($url);
    $responseKeys = json_decode($response, true);

    // Проверка на успешное прохождение капчи
    if(intval($responseKeys["success"]) !== 1) {
        echo 'Ошибка: капча не пройдена. Пожалуйста, попробуйте еще раз.';
        exit;
    }
} else {
    echo 'Ошибка: капча не заполнена.';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Заголовки для отправки письма с кодировкой UTF-8
    $boundary = md5(time());
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Тело письма с разделителем для вложений
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= "
    <html>
    <body>
        <h2>Заявка с формы обратной связи</h2>
        <p><strong>Имя:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Компания:</strong> $subject</p>
        <p><strong>Сообщение:</strong> $message</p>
    </body>
    </html>\r\n";

    // Добавляем прикрепленные файлы
    if (isset($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpFilePath) {
            if ($_FILES['files']['error'][$index] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['files']['name'][$index];
                $fileData = file_get_contents($tmpFilePath);
                $fileType = $_FILES['files']['type'][$index];

                $body .= "--$boundary\r\n";
                $body .= "Content-Type: $fileType; name=\"$fileName\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= chunk_split(base64_encode($fileData)) . "\r\n";
            }
        }
    }

    $body .= "--$boundary--";

    $to = "support@arma-t.ru";  // Укажите ваш email
    $subject = "Application from $subject";

    if (mail($to, $subject, $body, $headers)) {
        http_response_code(200);  // Сообщение об успешной отправке
    } else {
        http_response_code(500);  // Сообщение об ошибке
    }
}
?>
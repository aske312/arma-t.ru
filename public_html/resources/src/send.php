<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверка reCAPTCHA
    $recaptchaSecret = '6LcQqHYqAAAAAOjcdrqmn1L_EmarSeYkbVOGx0lN'; // Замените на ваш секретный ключ reCAPTCHA
    $recaptchaResponse = $_POST['g-recaptcha-response'];  // Получаем ответ от капчи
    $userIP = $_SERVER['REMOTE_ADDR'];  // IP пользователя

    // Проверяем ответ капчи через API Google
    $recaptchaVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaResponseJson = file_get_contents(
        $recaptchaVerifyUrl . '?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse . '&remoteip=' . $userIP
    );

    $recaptchaResult = json_decode($recaptchaResponseJson);

    if (!$recaptchaResult->success) {
        // Если капча не прошла проверку, возвращаем ошибку
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Ошибка проверки капчи.']);
        exit;
    }

    // Получаем данные формы
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

    $to = "info@arma-t.ru";  // Укажите ваш email
    $emailSubject = "Application from $subject";

    // Отправляем письмо
    if (mail($to, $emailSubject, $body, $headers)) {
        http_response_code(200);  // Сообщение об успешной отправке
        echo json_encode(['status' => 'success', 'message' => 'Ваше сообщение было успешно отправлено!']);
    } else {
        http_response_code(500);  // Сообщение об ошибке
        echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка при отправке сообщения.']);
    }
}
?>

<?php
session_start(); // Используем сессии для хранения времени последней отправки

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentTime = time(); // Текущее время в секундах
    $lastSubmissionTime = $_SESSION['last_submission_time'] ?? 0; // Время последней отправки
    $cooldownPeriod = 180; // Время блокировки в секундах (3 минуты)

    // Проверяем, прошло ли достаточно времени с момента последней отправки
    if ($currentTime - $lastSubmissionTime < $cooldownPeriod) {
        $remainingTime = $cooldownPeriod - ($currentTime - $lastSubmissionTime); // Оставшееся время
        http_response_code(429); // Код ответа "Too Many Requests"
        echo json_encode([
            'status' => 'error',
            'message' => 'Пожалуйста, подождите ' . $remainingTime . ' секунд перед повторной отправкой.'
        ]);
        exit; // Прекращаем выполнение скрипта
    }

    // Если лимит не превышен, продолжаем обработку формы
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
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Charset=UTF-8\r\n";

    // Тело письма с разделителем для вложений
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= "
    <html>
    <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
    </head>
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
    $emailSubject = "=?UTF-8?B?" . base64_encode("Заявка с формы обратной связи: $subject") . "?=";

    // Отправляем письмо
    if (mail($to, $emailSubject, $body, $headers)) {
        $_SESSION['last_submission_time'] = $currentTime; // Сохраняем время отправки
        http_response_code(200);  // Сообщение об успешной отправке
        echo json_encode(['status' => 'success', 'message' => 'Ваше сообщение было успешно отправлено!']);
    } else {
        http_response_code(500);  // Сообщение об ошибке
        echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка при отправке сообщения.']);
    }
}
?>
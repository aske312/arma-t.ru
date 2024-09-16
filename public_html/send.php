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

    // Проверка файла
    $file_attached = false;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];
        $file_type = $_FILES['file']['type'];
        $file_size = $_FILES['file']['size'];
        $file_attached = true;

        // Ограничение на размер файла (например, 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'Файл слишком большой (максимум 2MB)']);
            exit;
        }
    }

    // Настройки почты
    $to = 'your-email@sweb.ru';  // Здесь ваш email на хостинге sweb.ru
    $headers = "From: no-reply@your-domain.ru\r\n" .
               "Reply-To: $email\r\n" .
               "Content-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"\r\n";

    $subject_mail = "Заявка с сайта: $subject";

    // Основное сообщение
    $body_message = "Имя: $name\nE-mail: $email\nКомпания: $subject\nСообщение: $message";

    // Если файл прикреплен, добавляем его в тело письма
    if ($file_attached) {
        $attachment = chunk_split(base64_encode(file_get_contents($file_tmp)));
        $body = "
            --PHP-mixed-$random_hash
            Content-Type: text/plain; charset=\"utf-8\"
            Content-Transfer-Encoding: 7bit

            $body_message

            --PHP-mixed-$random_hash
            Content-Type: $file_type; name=\"$file_name\"
            Content-Transfer-Encoding: base64
            Content-Disposition: attachment

            $attachment
            --PHP-mixed-$random_hash--";
    } else {
        // Если файл не прикреплен, просто отправляем текстовое сообщение
        $body = $body_message;
    }

    // Отправка почты
    if (mail($to, $subject_mail, $body, $headers)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка при отправке сообщения']);
    }
}
?>
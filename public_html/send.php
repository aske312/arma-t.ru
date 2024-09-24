<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    // Проверка на обязательные поля
    if (empty($name) || empty($email) || empty($subject)) {
        echo "Пожалуйста, заполните все обязательные поля.";
        exit;
    }

    // Формирование заголовков письма
    $to = "support@arma-t.ru";  // Укажите ваш email
    $from = $email;
    $subject_email = "Новая заявка от $name ($subject)";

    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"boundary\"\r\n";

    // Основное сообщение
    $body = "--boundary\r\n";
    $body .= "Content-Type: text/plain; charset=utf-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= "Имя: $name\r\n";
    $body .= "Email: $email\r\n";
    $body .= "Название компании: $subject\r\n";
    $body .= "Комментарий: $message\r\n\r\n";

    // Обработка файлов
    if (!empty($_FILES['files']['name'][0])) {
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            $file_name = $_FILES['files']['name'][$i];
            $file_tmp_name = $_FILES['files']['tmp_name'][$i];
            $file_size = $_FILES['files']['size'][$i];
            $file_type = $_FILES['files']['type'][$i];
            $file_error = $_FILES['files']['error'][$i];

            // Ограничения на тип файлов
            $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            if (in_array($file_type, $allowed_types) && $file_error == 0) {
                $file_content = chunk_split(base64_encode(file_get_contents($file_tmp_name)));
                $body .= "--boundary\r\n";
                $body .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= "$file_content\r\n\r\n";
            } else {
                echo "Ошибка загрузки файла $file_name";
                exit;
            }
        }
    }

    $body .= "--boundary--";

    // Отправка письма
    if (mail($to, $subject_email, $body, $headers)) {
        echo "Ваше сообщение было успешно отправлено!";
    } else {
        echo "Произошла ошибка при отправке сообщения.";
    }
}
?>
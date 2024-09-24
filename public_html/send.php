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

    if (mail($to, $subject, $body, $headers)) {
        http_response_code(200);  // Сообщение об успешной отправке
    } else {
        http_response_code(500);  // Сообщение об ошибке
    }
}
?>
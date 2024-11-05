<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $company = htmlspecialchars($_POST['company']);
    $inn = htmlspecialchars($_POST['inn']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);

    // Email headers for UTF-8 encoding
    $boundary = md5(time());
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    // Email body with form data
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= "
    <html>
    <body>
        <h2>Заявка с формы обратной связи</h2>
        <p><strong>Имя:</strong> $name</p>
        <p><strong>Телефон:</strong> $phone</p>
        <p><strong>Компания:</strong> $company</p>
        <p><strong>ИНН:</strong> $inn</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Адрес:</strong> $address</p>
    </body>
    </html>\r\n";

    // Attach files if uploaded
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

    // Recipient email and subject
    $to = "support@arma-t.ru";
    $subject = "New order in $company";

    // Send the email and respond with status
    if (mail($to, $subject, $body, $headers)) {
        http_response_code(200);  // Successfully sent
    } else {
        http_response_code(500);  // Error occurred
    }
}
?>
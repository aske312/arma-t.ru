<?php
// Установим заголовок для возвращаемого JSON
header('Content-Type: application/json; charset=utf-8');

// Массив для хранения сообщений
$response = array();

// Проверяем, есть ли данные в POST-запросе
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверка полей формы
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : null;
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : null;
    $subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8') : null;
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8') : null;

    // Проверяем, что все поля заполнены
    if (!$name || !$email || !$subject || !$message) {
        $response['success'] = false;
        $response['error'] = "Все поля обязательны для заполнения.";
        echo json_encode($response);
        exit;
    }

    // Обработка файлов
    $allowedFormats = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    $maxFiles = 3;

    // Если файлы прикреплены
    if (isset($_FILES['file']) && count($_FILES['file']['name']) > 0) {
        $uploadedFiles = array();

        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            $fileName = $_FILES['file']['name'][$i];
            $fileTmpName = $_FILES['file']['tmp_name'][$i];
            $fileSize = $_FILES['file']['size'][$i];
            $fileType = $_FILES['file']['type'][$i];
            $fileError = $_FILES['file']['error'][$i];

            if ($fileError === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filePath = $uploadDir . uniqid() . '_' . basename($fileName);
                move_uploaded_file($fileTmpName, $filePath);
                $uploadedFiles[] = $filePath;
            }
        }
    }

    // Отправляем письмо с указанием кодировки
    $to = "support@arma-t.ru"; // Замените на ваш email
    $subjectMail = "Новая заявка: $subject";
    $body = "Имя: $name\nEmail: $email\nКомпания: $subject\nСообщение: $message\n";

    // Добавляем информацию о файлах в письмо
    if (!empty($uploadedFiles)) {
        $body .= "\nПрикрепленные файлы:\n";
        foreach ($uploadedFiles as $file) {
            $body .= $file . "\n";
        }
    }

    // Заголовки письма с кодировкой
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

    // Отправляем письмо
    if (mail($to, $subjectMail, $body, $headers)) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['error'] = "Ошибка отправки письма.";
    }

    echo json_encode($response);
    exit;
}
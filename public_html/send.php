<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path/to/PHPMailer/src/Exception.php';
require 'path/to/PHPMailer/src/PHPMailer.php';
require 'path/to/PHPMailer/src/SMTP.php';

header('Content-Type: application/json; charset=utf-8');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : null;
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : null;
    $subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8') : null;
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8') : null;

    if (!$name || !$email || !$subject || !$message) {
        $response['success'] = false;
        $response['error'] = "Все поля обязательны для заполнения.";
        echo json_encode($response);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Настройки сервера
        $mail->isSMTP();
        $mail->Host = 'smtp.spaceweb.ru'; // Укажите ваш SMTP-сервер
        $mail->SMTPAuth = true;
        $mail->Username = 'support@arma-t.ru'; // Ваш SMTP логин
        $mail->Password = 'SUS5SA94DcFY66H*'; // Ваш SMTP пароль
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 465; // Порт

        // Получатель и отправитель
        $mail->setFrom('no-reply@arma-t.ru', 'Отправитель');
        $mail->addAddress('support@arma-t.ru', 'Получатель');

        // Тема письма
        $mail->Subject = 'Новая заявка: ' . $subject;

        // Текст письма
        $body = "Имя: $name\nEmail: $email\nКомпания: $subject\nСообщение: $message\n";
        $mail->Body = $body;

        // Прикрепляем файлы, если они есть
        if (isset($_FILES['file']) && count($_FILES['file']['name']) > 0) {
            for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
                $fileName = $_FILES['file']['name'][$i];
                $fileTmpName = $_FILES['file']['tmp_name'][$i];

                // Добавляем файл как вложение
                if (is_uploaded_file($fileTmpName)) {
                    $mail->addAttachment($fileTmpName, $fileName);
                }
            }
        }

        // Отправляем письмо
        if ($mail->send()) {
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['error'] = "Ошибка отправки письма: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['error'] = "Ошибка: {$mail->ErrorInfo}";
    }

    echo json_encode($response);
}
?>
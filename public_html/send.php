<?php
// Установим заголовок для возвращаемого JSON
header('Content-Type: application/json');

// Массив для хранения сообщений
$response = array();

// Проверяем, есть ли данные в POST-запросе
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверка полей формы
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : null;
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : null;
    $subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : null;
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : null;

    // Проверяем, что все поля заполнены
    if (!$name || !$email || !$subject || !$message) {
        $response['success'] = false;
        $response['error'] = "Все поля обязательны для заполнения.";
        echo json_encode($response);
        exit;
    }

    // Обработка файлов (если они есть)
    $allowedFormats = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    $maxFiles = 3;

    // Если файлы прикреплены
    if (isset($_FILES['file']) && count($_FILES['file']['name']) > 0) {
        // Проверяем количество файлов
        if (count($_FILES['file']['name']) > $maxFiles) {
            $response['success'] = false;
            $response['error'] = "Максимум 3 файла.";
            echo json_encode($response);
            exit;
        }

        // Массив для хранения путей загруженных файлов
        $uploadedFiles = array();

        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            $fileName = $_FILES['file']['name'][$i];
            $fileTmpName = $_FILES['file']['tmp_name'][$i];
            $fileSize = $_FILES['file']['size'][$i];
            $fileType = $_FILES['file']['type'][$i];
            $fileError = $_FILES['file']['error'][$i];

            // Проверяем ошибки загрузки
            if ($fileError !== UPLOAD_ERR_OK) {
                $response['success'] = false;
                $response['error'] = "Ошибка загрузки файла $fileName.";
                echo json_encode($response);
                exit;
            }

            // Проверяем формат файла
            if (!in_array($fileType, $allowedFormats)) {
                $response['success'] = false;
                $response['error'] = "Формат файла $fileName не поддерживается.";
                echo json_encode($response);
                exit;
            }

            // Проверяем размер файла
            if ($fileSize > $maxSize) {
                $response['success'] = false;
                $response['error'] = "Файл $fileName превышает максимальный размер в 2MB.";
                echo json_encode($response);
                exit;
            }

            // Путь для сохранения файла
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); // Создаём директорию, если её нет
            }

            // Уникальное имя для файла
            $filePath = $uploadDir . uniqid() . '_' . basename($fileName);

            // Перемещаем файл из временной директории
            if (move_uploaded_file($fileTmpName, $filePath)) {
                $uploadedFiles[] = $filePath; // Добавляем файл в список загруженных
            } else {
                $response['success'] = false;
                $response['error'] = "Ошибка сохранения файла $fileName.";
                echo json_encode($response);
                exit;
            }
        }
    }

    // Теперь можно обработать отправку письма (например, через mail() или другой сервис)
    // В примере используем стандартную функцию mail()
    $to = "support@arma-t.ru"; // Замените на ваш email
    $subject = "Новая заявка: $subject";
    $body = "Имя: $name\nEmail: $email\nКомпания: $subject\nСообщение: $message\n";

    // Добавляем информацию о файлах в сообщение
    if (!empty($uploadedFiles)) {
        $body .= "\nПрикрепленные файлы:\n";
        foreach ($uploadedFiles as $file) {
            $body .= $file . "\n";
        }
    }

    // Отправляем письмо (без файлов)
    if (mail($to, $subject, $body)) {
        $response['success'] = true;
    } else {
        $response['success'] = false;
        $response['error'] = "Ошибка отправки письма.";
    }

    echo json_encode($response);
    exit;
}
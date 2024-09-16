<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Application;
use Bitrix\Main\IO\File;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

// Подключаем модуль почты
Loader::includeModule("main");

$request = Application::getInstance()->getContext()->getRequest();
$response = [];

// Получаем данные формы
$name = htmlspecialchars(trim($request->getPost("name")));
$email = htmlspecialchars(trim($request->getPost("email")));
$subject = htmlspecialchars(trim($request->getPost("subject")));
$message = htmlspecialchars(trim($request->getPost("message")));
$files = $request->getFile("file");

// Проверка обязательных полей
if (!$name || !$email || !$subject) {
    $response = ["status" => "error", "message" => "Заполните обязательные поля!"];
    echo json_encode($response);
    exit;
}

// Формируем массив для почтового события
$arFields = [
    "NAME" => $name,
    "EMAIL" => $email,
    "SUBJECT" => $subject,
    "MESSAGE" => $message ?: "Комментарий отсутствует",
];

// Обработка прикрепленных файлов
$attachments = [];
if ($files && is_array($files["tmp_name"])) {
    foreach ($files["tmp_name"] as $key => $tmpName) {
        if (is_uploaded_file($tmpName)) {
            $fileArray = \CFile::MakeFileArray($tmpName);
            $fileArray['name'] = $files['name'][$key];
            $fileID = \CFile::SaveFile($fileArray, "feedback_files");
            if ($fileID) {
                $attachments[] = $fileID;
            }
        }
    }
}

// Отправляем почтовое событие с файлами (если есть)
$result = Event::sendImmediate([
    "EVENT_NAME" => "FORM_FEEDBACK",
    "LID" => "s1",
    "C_FIELDS" => $arFields,
    "FILE" => $attachments, // Добавляем вложения, если они есть
]);

// Проверяем результат и отправляем ответ
if ($result) {
    $response = ["status" => "success", "message" => "Заявка успешно отправлена"];
} else {
    $response = ["status" => "error", "message" => "Ошибка при отправке"];
}

echo json_encode($response);
?>

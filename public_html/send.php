<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Application;

// Проверяем подключение Битрикса
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

// Подключаем модуль почты
Loader::includeModule("main");

$request = Application::getInstance()->getContext()->getRequest();

$name = $request->getPost("name");
$email = $request->getPost("email");
$subject = $request->getPost("subject");
$message = $request->getPost("message");
$files = $request->getFile("file");

// Валидация данных
if (empty($name) || empty($email) || empty($subject)) {
    echo json_encode(["status" => "error", "message" => "Обязательные поля не заполнены"]);
    exit;
}

// Формируем данные для почтового события
$arFields = array(
    "NAME" => $name,
    "EMAIL" => $email,
    "SUBJECT" => $subject,
    "MESSAGE" => $message,
    "FILES" => []
);

// Если прикреплены файлы, добавляем их в массив
if (!empty($files)) {
    foreach ($files['tmp_name'] as $key => $tmpName) {
        if (is_uploaded_file($tmpName)) {
            $fileArray = CFile::MakeFileArray($tmpName);
            $fileArray['name'] = $files['name'][$key];
            $fileID = CFile::SaveFile($fileArray, "form_files");
            $arFields["FILES"][] = $fileID;
        }
    }
}

// Отправляем почтовое событие
$result = Event::sendImmediate(array(
    "EVENT_NAME" => "FORM_FEEDBACK",
    "LID" => "s1",
    "C_FIELDS" => $arFields,
    "FILE" => $arFields["FILES"]
));

if ($result) {
    echo json_encode(["status" => "success", "message" => "Заявка успешно отправлена"]);
} else {
    echo json_encode(["status" => "error", "message" => "Ошибка при отправке"]);
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Логируем полученные данные
    error_log("Полученные данные: " . print_r($_POST, true));

    // Получаем данные из формы и обрабатываем их
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $company = htmlspecialchars($_POST['company']);
    $inn = htmlspecialchars($_POST['inn']);
    $cartData = json_decode($_POST['cartData'], true);

    // Проверяем, что данные корзины были переданы и декодированы
    if (json_last_error() !== JSON_ERROR_NONE || !isset($cartData['cartItems'])) {
        error_log("Ошибка декодирования JSON: " . json_last_error_msg());
        echo "Ошибка: данные корзины неверны или отсутствуют.";
        exit;
    }

    // Логируем данные корзины
    error_log("Данные корзины: " . print_r($cartData, true));

    // Формируем текст письма
    $message = "Новый заказ:\n\n";
    $message .= "Имя: $name\n";
    $message .= "Телефон: $phone\n";
    $message .= "Email: $email\n";
    $message .= "Компания: $company\n";
    $message .= "ИНН: $inn\n";
    $message .= "Адрес доставки: $address\n\n";
    $message .= "Товары:\n";

    $totalSum = 0;
    foreach ($cartData['cartItems'] as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $totalSum += $itemTotal;
        $message .= "- {$item['name']} (Артикул: {$item['article']}) - {$item['quantity']} шт. x {$item['price']} ₽ = {$itemTotal} ₽\n";
    }

    $message .= "\nИтоговая сумма: {$totalSum} ₽";

    // Устанавливаем заголовки для отправки письма с кодировкой UTF-8
    $headers = "From: no-reply@arma-t.ru\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";  // Указываем кодировку UTF-8
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";  // Указываем кодировку для передачи текста
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    // Отправка письма
    $to = "info@arma-t.ru"; // Замените на ваш email
    $subject = "=?UTF-8?B?" . base64_encode("Новый заказ с сайта") . "?=";  // Кодируем тему письма в UTF-8

    if (mail($to, $subject, $message, $headers)) {
        echo "Заказ успешно оформлен!";
    } else {
        echo "Ошибка при отправке заказа.";
    }
} else {
    echo "Некорректный запрос.";
}
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $cartData = json_decode($_POST['cartData'], true);

    // Формируем текст письма
    $message = "Новый заказ:\n\n";
    $message .= "Имя: $name\n";
    $message .= "Телефон: $phone\n";
    $message .= "Email: $email\n";
    $message .= "Адрес доставки: $address\n\n";
    $message .= "Товары:\n";

    $totalSum = 0;
    foreach ($cartData['cartItems'] as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $totalSum += $itemTotal;
        $message .= "- {$item['name']} (Артикул: {$item['article']}) - {$item['quantity']} шт. x {$item['price']} ₽ = {$itemTotal} ₽\n";
    }

    $message .= "\nИтоговая сумма: {$totalSum} ₽";

    // Отправка письма
    $to = "info@arma-t.ru"; // Замените на ваш email
    $subject = "Новый заказ с сайта";
    $headers = "From: no-reply@arma-t.ru";

    if (mail($to, $subject, $message, $headers)) {
        echo "Заказ успешно оформлен!";
    } else {
        echo "Ошибка при отправке заказа.";
    }
} else {
    echo "Некорректный запрос.";
}
?>
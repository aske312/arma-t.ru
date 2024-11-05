<?php
// Ваш Secret Key для hCaptcha
$secret = 'ES_822e9148d2e3439baf2fdbb7592e574a';
$response = $_POST['h-captcha-response'];  // Ответ от пользователя на капчу
$remoteip = $_SERVER['REMOTE_ADDR'];  // IP адрес пользователя

// Проверяем, пришел ли ответ капчи
if (empty($response)) {
    echo json_encode(['status' => 'error', 'message' => 'Пожалуйста, подтвердите, что вы не робот.']);
    exit;
}

// Отправляем запрос на сервер hCaptcha для проверки капчи
$verifyUrl = 'https://hcaptcha.com/siteverify';
$verifyResponse = file_get_contents($verifyUrl . '?secret=' . $secret . '&response=' . $response . '&remoteip=' . $remoteip);
$responseKeys = json_decode($verifyResponse, true);

// Если капча не прошла проверку
if (intval($responseKeys["success"]) !== 1) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при верификации капчи. Пожалуйста, попробуйте снова.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $company = htmlspecialchars($_POST['company']);
    $inn = htmlspecialchars($_POST['inn']);
    $address = htmlspecialchars($_POST['address']);
    $cartData = json_decode($_POST['cartData'], true);

    $boundary = md5(time());
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= "<html><body>";
    $body .= "<h2>Новый заказ от $name</h2>";
    $body .= "<p><strong>Телефон:</strong> $phone</p>";
    $body .= "<p><strong>Компания:</strong> $company</p>";
    $body .= "<p><strong>ИНН:</strong> $inn</p>";
    $body .= "<p><strong>Email:</strong> $email</p>";
    $body .= "<p><strong>Адрес:</strong> $address</p>";
    $body .= "<h3>Товары:</h3><ul>";

    foreach ($cartData['cartItems'] as $item) {
        $body .= "<li>{$item['name']} (Артикул: {$item['article']}): {$item['quantity']} шт. Цена за ед.: {$item['price']}₽</li>";
    }

    $body .= "</ul></body></html>";
    $body .= "--$boundary--\r\n";

    $to = 'support@arma-t.ru';
    $subject = "New order in $name";

    // Отправка письма
    if (mail($to, $subject, $body, $headers)) {
        echo 'Ваша заявка оформлена!';
    } else {
        echo 'Ошибка: Упс. Что-то пошло не так, обратитесь в support@arma-t.ru';
    }
}
?>
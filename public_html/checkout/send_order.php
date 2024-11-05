<?php
$secret = '6Lca1HUqAAAAANhTUDGv7N3sp7RdabodoCoHCNrX';

if (isset($_POST['g-recaptcha-response'])) {
    $recaptchaResponse = $_POST['g-recaptcha-response'];
    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $recaptchaResponse;

    $response = file_get_contents($url);
    $responseKeys = json_decode($response, true);

    if (intval($responseKeys["success"]) !== 1) {
        echo 'Ошибка: капча не пройдена. Пожалуйста, попробуйте еще раз.';
        exit;
    }
} else {
    echo 'Ошибка: капча не заполнена.';
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
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars_decode($_POST['name']);
    $email = htmlspecialchars_decode($_POST['email']);
    $phone = htmlspecialchars_decode($_POST['phone']);
    $company = htmlspecialchars_decode($_POST['company']);
    $inn = htmlspecialchars_decode($_POST['inn']);
    $address = htmlspecialchars_decode($_POST['address']);
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

    $to = 'info@arma-t.ru';
    $subject = "Новый заказ от $name";
    mail($to, $subject, $body, $headers);
}
?>
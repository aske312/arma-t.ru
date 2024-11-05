<?php
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

    // Create email body
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= "<html><body>";
    $body .= "<h2>Заявка с формы обратной связи</h2>";
    $body .= "<p><strong>Имя:</strong> $name</p>";
    $body .= "<p><strong>Телефон:</strong> $phone</p>";
    $body .= "<p><strong>Компания:</strong> $company</p>";
    $body .= "<p><strong>ИНН:</strong> $inn</p>";
    $body .= "<p><strong>Email:</strong> $email</p>";
    $body .= "<p><strong>Адрес:</strong> $address</p>";

    // Format cart data
    $body .= "<h3>Содержимое корзины:</h3><ul>";
    foreach ($cartData['cartItems'] as $item) {
        $body .= "<li><strong>{$item['name']}</strong> - Артикул: {$item['article']}, Количество: {$item['quantity']}, Цена: {$item['price']} руб.</li>";
    }
    $body .= "</ul>";

    $body .= "</body></html>\r\n";
    $body .= "--$boundary--";

    $to = "support@arma-t.ru";
    $subject = "New order in $name";

    if (mail($to, $subject, $body, $headers)) {
        http_response_code(200);
    } else {
        http_response_code(500);
    }
}
?>

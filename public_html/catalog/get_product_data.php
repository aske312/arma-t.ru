<?php
// Подключение к Bitrix API
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка наличия данных о товарах
    if (isset($_POST['cartItems'])) {
        // Декодируем данные корзины из JSON
        $cartItems = json_decode($_POST['cartItems'], true);

        if (!$cartItems) {
            echo json_encode(["error" => "Invalid product data"]);
            exit;
        }

        // Формируем данные для корзины
        $basketData = [];

        foreach ($cartItems as $cartItem) {
            // Получаем данные из куки
            $basketData[] = [
                "id" => $cartItem['id'],
                "name" => $cartItem['name'] ?? 'Неизвестный товар', // Используем имя из куки или подставляем значение по умолчанию
                "price" => (float)($cartItem['price'] ?? 0), // Цену, если указана
                "quantity" => (int)($cartItem['quantity'] ?? 1), // Количество
                "total" => (float)($cartItem['price'] ?? 0) * (int)($cartItem['quantity'] ?? 1), // Итоговая сумма
                "picture" => $cartItem['picture'] ?? '' // Путь к изображению, если есть
            ];
        }

        // Возвращаем данные корзины в формате JSON
        echo json_encode($basketData);
    } else {
        echo json_encode(["error" => "No cart items provided"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

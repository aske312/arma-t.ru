<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Описание страницы для SEO">
    <meta name="keywords" content="Ключевые слова для SEO">
    <?$APPLICATION->ShowHead();?>
    <title><?$APPLICATION->ShowTitle();?></title>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
</head>
    <body>
        <div id="panel"> <?$APPLICATION->ShowPanel();?> </div>
        <header id="siteHeader">
            <div class="header-content">
                <div class="logo">
                    <a href="/"><img src="/local/img/logo/resource_1.png" alt="My Logo"></a>
                </div>
                <div class="nav-search">
                    <button class="menu-toggle" onclick="toggleMenu()">&#9776;</button>
                    <nav id="mainNav">
                        <a href="/#Company">О компании</a>
                        <a href="/catalog/">Каталог</a>
                        <a href="/#Contact">Контакты</a>
                        <a href="/#Delivery">Доставка</a>
                        <a href="/#Cash">Оплата</a>
                    </nav>
                    <!--
                        <?php $APPLICATION->IncludeComponent(
                            "search",
                            "",
                            Array(
                                "ACTION_VARIABLE" => "action",
                                "AJAX_MODE" => "N",
                                "AJAX_OPTION_ADDITIONAL" => "",
                                "AJAX_OPTION_HISTORY" => "N",
                                "AJAX_OPTION_JUMP" => "N",
                                "AJAX_OPTION_STYLE" => "Y",
                                "BASKET_URL" => "/personal/basket.php",
                                "CACHE_TIME" => "36000000",
                                "CACHE_TYPE" => "A",
                                "CHECK_DATES" => "N",
                                "DETAIL_URL" => "",
                                "DISPLAY_BOTTOM_PAGER" => "Y",
                                "DISPLAY_COMPARE" => "N",
                                "DISPLAY_TOP_PAGER" => "N",
                                "ELEMENT_SORT_FIELD" => "sort",
                                "ELEMENT_SORT_FIELD2" => "id",
                                "ELEMENT_SORT_ORDER" => "asc",
                                "ELEMENT_SORT_ORDER2" => "desc",
                                "IBLOCK_ID" => "",
                                "IBLOCK_TYPE" => "catalog",
                                "LINE_ELEMENT_COUNT" => "3",
                                "NO_WORD_LOGIC" => "N",
                                "OFFERS_LIMIT" => "5",
                                "PAGER_DESC_NUMBERING" => "N",
                                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                "PAGER_SHOW_ALL" => "N",
                                "PAGER_SHOW_ALWAYS" => "N",
                                "PAGER_TEMPLATE" => ".default",
                                "PAGER_TITLE" => "Товары",
                                "PAGE_ELEMENT_COUNT" => "30",
                                "PRICE_CODE" => array(),
                                "PRICE_VAT_INCLUDE" => "Y",
                                "PRODUCT_ID_VARIABLE" => "id",
                                "PRODUCT_PROPERTIES" => array(),
                                "PRODUCT_PROPS_VARIABLE" => "prop",
                                "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                                "PROPERTY_CODE" => array("",""),
                                "RESTART" => "N",
                                "SECTION_ID_VARIABLE" => "SECTION_ID",
                                "SECTION_URL" => "",
                                "SHOW_PRICE_COUNT" => "1",
                                "USE_LANGUAGE_GUESS" => "Y",
                                "USE_PRICE_COUNT" => "N",
                                "USE_PRODUCT_QUANTITY" => "N",
                                "USE_SEARCH_RESULT_ORDER" => "N",
                                "USE_TITLE_RANK" => "N"
                            )
                        );?> -->
                </div>
                <div class="contact-info">
                    <p><a href="tel:+70000000000" class="phone-link">+7 (000) 000-00-00</a></p>
                    <button onclick="window.location.href='#Cash'">Оставить заявку</button>

                    <!-- Кнопка корзины -->
                    <button id="basket-button">Показать корзину</button>

                    <!-- Корзина -->
                    <div id="basket-popup" class="basket-popup hidden">
                        <div class="basket">
                            <h3>Корзина</h3>

                            <!-- Секция для товаров -->
                            <div id="basket-items">
                                <?php if (!empty($cartItems)) : ?>
                                    <?php foreach ($cartItems as $item) : ?>
                                        <?php
                                            // Получаем данные о товаре
                                            $itemID = $item['ID'];
                                            $itemName = $item['EL_SH_NAME'];
                                            $itemPrice = $item['EL_PRICE'];
                                            $itemQuantity = $item['QUANTITY'];
                                            $itemPicture = !empty($item['EL_PICTURE']) ? $item['EL_PICTURE'] : '/path/to/default/section/picture.jpg'; // Если нет изображения, используем изображение раздела
                                            $itemTotalPrice = $itemPrice * $itemQuantity;
                                        ?>
                                        <!-- Карточка товара -->
                                        <div class="basket-item">
                                            <img src="<?= $itemPicture ?>" alt="<?= $itemName ?>" class="item-picture">
                                            <div class="item-details">
                                                <h4 class="item-name"><?= $itemName ?></h4>
                                                <p class="item-price">Цена: <?= $itemPrice ?> руб.</p>
                                                <p class="item-quantity">Количество: <?= $itemQuantity ?></p>
                                                <p class="item-total-price">Стоимость: <?= $itemTotalPrice ?> руб.</p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <!-- Сообщение, если товаров нет -->
                                    <p id="empty-message">В корзине нет товаров.</p>
                                <?php endif; ?>
                            </div>

                            <p>Итоговая стоимость: <span id="total-price">
                                <?php
                                $totalPrice = 0;
                                foreach ($cartItems as $item) {
                                    $totalPrice += $item['EL_PRICE'] * $item['QUANTITY'];
                                }
                                echo $totalPrice;
                                ?>
                            </span> руб.</p>

                            <button id="checkout">Оформить заказ</button>
                            <button id="clear-cart">Очистить корзину</button>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <script>
            document.getElementById('basket-button').addEventListener('click', function () {
                const basketPopup = document.getElementById('basket-popup');
                const basketItems = document.getElementById('basket-items');
                const emptyMessage = document.getElementById('empty-message');

                // Проверяем, есть ли товары в корзине
                if (basketItems.children.length === 0) {
                    emptyMessage.classList.remove('hidden');
                    basketPopup.classList.add('hidden');
                    alert("В корзине нет товаров.");
                } else {
                    emptyMessage.classList.add('hidden');
                    basketPopup.classList.toggle('hidden');
                }
            });

            // Пример для очистки корзины (для демонстрации)
            document.getElementById('clear-cart').addEventListener('click', function () {
                document.getElementById('basket-items').innerHTML = '';
                document.getElementById('total-price').textContent = '0';
                alert("Корзина очищена.");
            });
        </script>
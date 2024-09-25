<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog.php");
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Подключение стилей и скриптов
Asset::getInstance()->addCss("/local/css/catalog.css");
Asset::getInstance()->addCss("/local/css/header.css");
Asset::getInstance()->addCss("/local/css/footer.css");
Asset::getInstance()->addJs("/local/js/script.js");
?>

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

                    <!-- Корзина -->
                    <div class="basket-container">
                        <button id="basketButton" style="display:none;" onclick="toggleBasketDropdown()">В Корзине: <span id="basketCount">0</span></button>
                        <div id="basketDropdown" class="basket-dropdown" style="display:none;">
                            <div id="basketItems"></div>
                            <div id="basketTotal"></div>
                            <button onclick="checkout()">Оформить заказ</button>
                            <button onclick="clearBasket()">Очистить корзину</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <script>

        </script>

        <style>
            .cart-wrapper {
                position: relative;
                display: inline-block;
            }

            .cart-dropdown {
                display: none;
                position: absolute;
                right: 0;
                background: white;
                box-shadow: 0 8px 16px rgba(0,0,0,0.3);
                width: 300px;
                padding: 10px;
            }

            .cart-dropdown.hidden {
                display: none;
            }

            .cart-info, .cart-total {
                margin: 10px 0;
            }

            #cart-items {
                list-style-type: none;
                padding: 0;
            }

            #cart-items li {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
            }

            .cart-btn {
                margin-left: 10px;
            }
        </style>
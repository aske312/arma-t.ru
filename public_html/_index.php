<<<<<<< HEAD
<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("404 Not Found");

$APPLICATION->IncludeComponent("bitrix:main.map", ".default", Array(
	"LEVEL"	=>	"3",
	"COL_NUM"	=>	"2",
	"SHOW_DESCRIPTION"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"CACHE_TIME"	=>	"36000000"
	)
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
=======
<?php
// Файл, например, maintenance.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Ведутся технические работы</title>
  <style>
    /* Сбрасываем отступы и задаём высоту для выравнивания по центру */
    html, body {
      height: 100%;
      margin: 0;
      background: #f4f4f4;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: Arial, sans-serif;
    }
    .container {
      text-align: center;
    }
    /* Стиль для шестерёнки с использованием inline SVG как background */
    .gear {
      width: 100px;
      height: 100px;
      margin: 0 auto;
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="gray" d="M50 30a20 20 0 1 0 20 20A20 20 0 0 0 50 30zm0 35a15 15 0 1 1 15-15 15 15 0 0 1-15 15zm38 7h-8.1a32.4 32.4 0 0 0-2.3-7.6l5.7-5.7a3 3 0 0 0 0-4.2l-9.8-9.8a3 3 0 0 0-4.2 0l-5.7 5.7a32.4 32.4 0 0 0-7.6-2.3V17a3 3 0 0 0-3-3h-12a3 3 0 0 0-3 3v8.1a32.4 32.4 0 0 0-7.6 2.3l-5.7-5.7a3 3 0 0 0-4.2 0l-9.8 9.8a3 3 0 0 0 0 4.2l5.7 5.7a32.4 32.4 0 0 0-2.3 7.6H17a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h8.1a32.4 32.4 0 0 0 2.3 7.6l-5.7 5.7a3 3 0 0 0 0 4.2l9.8 9.8a3 3 0 0 0 4.2 0l5.7-5.7a32.4 32.4 0 0 0 7.6 2.3V83a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3v-8.1a32.4 32.4 0 0 0 7.6-2.3l5.7 5.7a3 3 0 0 0 4.2 0l9.8-9.8a3 3 0 0 0 0-4.2l-5.7-5.7a32.4 32.4 0 0 0 2.3-7.6H83a3 3 0 0 0 3-3z"/></svg>') no-repeat center center;
      background-size: contain;
      animation: spin 3s linear infinite;
    }
    /* Анимация вращения */
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    h1 {
      color: #333;
      margin-top: 20px;
    }
    p {
      color: #666;
      font-size: 1.1em;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="gear"></div>
    <h1>Ведутся работы на сайте</h1>
    <p>Пожалуйста, подождите...</p>
  </div>
</body>
</html>
>>>>>>> 5f3a3bfcaa976228ac3084cdedea5ea3cfc4aa9d

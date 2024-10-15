<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>

<?php
if (CModule::IncludeModule("iblock")) {
    $productId = intval($_GET['id']);
    $res = CIBlockElement::GetByID($productId);

    if ($ar_res = $res->GetNext()) {
        // Получаем основные параметры товара
        $productName = $ar_res['NAME'];
        $productDescription = $ar_res['DETAIL_TEXT'];
        $productImage = CFile::GetPath($ar_res['DETAIL_PICTURE']); // Изображение товара
        $productPrice = ''; // Здесь можно получить цену, если она хранится в свойствах или в другом инфоблоке
        $productArticul = ''; // Артикул
        $productAvailability = ''; // Срок изготовления

        // Получаем информацию о разделе
        $sectionId = $ar_res['IBLOCK_SECTION_ID'];
        $sectionRes = CIBlockSection::GetByID($sectionId);
        if ($section = $sectionRes->GetNext()) {
            $sectionImage = CFile::GetPath($section['PICTURE']); // Изображение раздела
            $sectionName = $section['NAME']; // Название раздела
        }

        // Массив для хранения характеристик
        $productProperties = [];

        // Получаем свойства товара
        $properties = CIBlockElement::GetProperty($ar_res['IBLOCK_ID'], $productId, array("sort" => "asc"), array());
        while ($prop = $properties->Fetch()) {
            if (!empty($prop['VALUE'])) {
                if ($prop['CODE'] === 'EL_ARTICUL') {
                    $productArticul = $prop['VALUE'];
                } elseif ($prop['CODE'] === 'EL_AVAILABILITY') {
                    $productAvailability = $prop['VALUE'];
                } else {
                    $productProperties[$prop['NAME']] = $prop['VALUE'];
                }
            }
        }

        // Условия для цены
        if (empty($productPrice) || $productPrice == 0) {
            $productPrice = "Под заказ";
        }

        // Теперь можно использовать переменные для построения шаблона
        ?>
        <div class="section-title">
            <h2><?php echo htmlspecialchars($sectionName); ?></h2>
            <p>Подробное описание: <?php echo htmlspecialchars($productName); ?></p>
        </div>

        <div class="product-detail">
            <div class="product-content">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($productImage ?: $sectionImage); ?>" alt="<?php echo htmlspecialchars($productName); ?>" style="width: 300px; height: auto;"> <!-- Изображение товара -->
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($productName); ?></h1>
                    <div class="product-articul">Артикул: <?php echo htmlspecialchars($productArticul); ?></div>
                    <div class="product-availability">Срок изготовления: <?php echo htmlspecialchars($productAvailability); ?></div>
                    <div class="product-price">
                        <p>Цена: <?php echo htmlspecialchars($productPrice); ?></p>
                    </div>
                    <button class="add-to-cart">В корзину</button>
                </div>
            </div>
            <?php if (!empty($productProperties)): ?>
                <div class="product-attributes">
                    <h2>Характеристики:</h2>
                    <ul>
                        <?php foreach ($productProperties as $propName => $propValue): ?>
                            <li><?php echo htmlspecialchars($propName) . ': ' . htmlspecialchars($propValue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .section-title {
                background: url('/local/img/block/pattern.png') no-repeat center;
                background-size: cover; /* Заполнение всей доступной области без растягивания */
                color: #fff;
                padding: 20px;
                text-align: left;
                margin-bottom: 20px;
                width: 1300px; /* Максимальная ширина контейнера */
                margin: 0 auto; /* Центрирование контейнера */
                height: 100%; /* Высота контейнера */
            }

            .section-title h2 {
                margin: 0;
                font-size: 24px;
            }

            .section-title p {
                margin-top: 10px;
                font-size: 16px;
                color: #ddd;
            }

            .product-detail {
                /* background: white; */
                max-width: 800px;
                margin: 0 auto;
                font-family: Arial, sans-serif;
            }
            .product-content {
                display: flex;
                margin-bottom: 20px;
            }
            .product-image {
                margin-right: 20px;
            }
            .product-info {
                flex: 1;
            }
            .product-price {
                font-size: 1.2em;
                color: #333;
            }
            .add-to-cart {
                background-color: #28a745;
                color: white;
                border: none;
                padding: 10px 15px;
                cursor: pointer;
                font-size: 1em;
                margin-top: 10px;
            }
            .add-to-cart:hover {
                background-color: #218838;
            }
            .product-attributes {
                margin-top: 20px;
            }
            .product-attributes ul {
                list-style-type: none;
                padding: 0;
            }
            .product-attributes li {
                margin-bottom: 5px;
            }
        </style>

        <?php
    } else {
        echo "Товар не найден.";
    }
}
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
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
        $productPrice = ''; // Цена
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
        ?>

        <div class="section-title">
            <h2><?php echo htmlspecialchars($productName); ?></h2> <!-- Название раздела -->
            <p>Подробное описание</p>
        </div>

        <div class="product-detail" style="background-color: white; border-radius: 5px; padding: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
            <div class="product-content">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($productImage ?: $sectionImage); ?>" alt="<?php echo htmlspecialchars($productName); ?>" style="width: 300px; height: auto;"> <!-- Изображение товара -->
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($productName); ?></h1>
                    <div class="product-articul-availability">Артикул: <?php echo htmlspecialchars($productArticul); ?> &nbsp; Срок изготовления: <?php echo htmlspecialchars($productAvailability); ?></div>
                    <!-- <div class="product-availability">Срок изготовления: <?php echo htmlspecialchars($productAvailability); ?></div> -->
                    <div class="product-price">
                        <p>Цена: <?php echo htmlspecialchars($productPrice); ?></p>
                    </div>
                    <button class="add-to-cart">В корзину</button>
                </div>
            </div>
            <?php if (!empty($productProperties)): ?>
                <div class="product-attributes">
                    <h2>Характеристики</h2>
                    <table>
                        <tbody>
                            <?php foreach ($productProperties as $propName => $propValue): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($propName); ?></td>
                                    <td><?php echo htmlspecialchars($propValue); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <button class="back-button" onclick="history.back()">Назад</button> <!-- Кнопка Назад -->
        </div>

        <style>
            .section-title {
                background: url('/local/img/block/pattern.png') no-repeat center;
                background-size: cover;
                color: #fff;
                padding: 20px;
                text-align: left;
                margin-bottom: 20px;
                width: 1300px;
                margin: 0 auto;
                height: 100%;
            }

            .section-title h2 {
                margin: 0;
                font-size: 24px;
            }

            .product-detail {
                max-width: 1200px;
                margin: 0 auto;
                font-family: Arial, sans-serif;
                color: #333;
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
                background-color: #2654A2;
                color: white;
                border: none;
                padding: 10px 15px;
                cursor: pointer;
                font-size: 1em;
                margin-top: 10px;
            }

            .add-to-cart:hover {
                background-color: #0056b3;
            }

            .product-attributes {
                margin-top: 20px;
            }

            .product-attributes table {
                width: 100%;
                border-collapse: collapse;
            }

            .product-attributes td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
                text-align: left; /* Выравнивание по левому краю */
            }

            .back-button {
                background-color: #2654A2;
                margin: 0 auto;
                color: white;
                border: none;
                padding: 10px 20px;
                cursor: pointer;
                font-size: 1em;
                margin-top: 20px;
                display: block;
            }

            .back-button:hover {
                background-color: #0056b3;
            }
        </style>

        <?php
    } else {
        echo "Товар не найден.";
    }
}
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
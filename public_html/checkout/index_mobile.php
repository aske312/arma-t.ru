<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Page\Asset;
Asset::getInstance()->addCss("/resources/css/checkout.css");
?>

<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(99876509, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true,
        webvisor:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/99876509" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<div class="checkout-title-m">
    <h2>Оформление заказа</h2>
    <p>Товары в корзине</p>
</div>

<div id="empty-cart-message" class="empty-cart-m" style="display: none;">
    <p>Вы пока что ничего не добавили в корзину.</p>
</div>

<div class="checkout-block-m" id="checkout-block" style="display: none;">
    <div id="cart-items">
        <div class="product-checkout-m" id="product-checkout"></div>
        <div class="total-price-m" id="total-amount"><strong>Итоговая сумма: 0 ₽</strong></div>
    </div>

    <div class="button-group-m">
        <button onclick="history.back()" class="back-button-m">Назад</button>
        <button id="order-btn" class="order-button-m">Оформить заказ</button>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        loadCart();
    });

    function loadCart() {
        let cartDataString = localStorage.getItem('cartItems');
        let cartItemsContainer = document.getElementById('product-checkout');
        let checkoutBlock = document.getElementById('checkout-block');
        let emptyCartMessage = document.getElementById('empty-cart-message');

        if (!cartDataString) {
            // Показываем сообщение о пустой корзине, скрываем блок с товарами
            emptyCartMessage.style.display = "block";
            checkoutBlock.style.display = "none";
            return;
        }

        let cartData = JSON.parse(cartDataString);

        if (!cartData || !Array.isArray(cartData.cartItems) || cartData.cartItems.length === 0) {
            emptyCartMessage.style.display = "block";
            checkoutBlock.style.display = "none";
            return;
        }

        // Показываем блок с корзиной, скрываем сообщение о пустой корзине
        emptyCartMessage.style.display = "none";
        checkoutBlock.style.display = "block";

        // Очищаем контейнер перед добавлением новых элементов
        cartItemsContainer.innerHTML = '';

        cartData.cartItems.forEach((item, index) => {
            let itemDiv = document.createElement('div');
            itemDiv.classList.add('cart-item');

            let imageHTML = item.image ?
                `<div class="product-image-m"><img src="${item.image}" alt="${item.name}"></div>` :
                `<div class="product-image-m"><img src="/resources/img/production/0.png" alt="Нет изображения"></div>`;

            let priceText = item.price && item.price !== "По запросу" ? `${item.price} ₽` : "По запросу";

            itemDiv.innerHTML = `
                ${imageHTML}
                <div class="product-info-m">
                    <span><strong>${item.name}</strong></span>
                    <span>Артикул: <strong>${item.article}</strong></span>
                    <span class="price-m">Цена за единицу: <strong>${priceText}</strong></span>
                    <span class="total-item-price-m">В сумме: <strong>${priceText}</strong></span>
                </div>
            `;
            cartItemsContainer.appendChild(itemDiv);
        });

        updateTotal(cartData.cartItems);
    }

    // Обновление итоговой суммы
    function updateTotal(cartItems) {
        let totalAmount = cartItems.reduce((total, item) => {
            let price = isNaN(parseFloat(item.price)) ? 0 : parseFloat(item.price);
            return total + (price * item.quantity);
        }, 0);

        document.getElementById('total-amount').innerText = `Итоговая сумма: ${totalAmount > 0 ? totalAmount.toFixed(2) + " ₽" : "По запросу"}`;
    }
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

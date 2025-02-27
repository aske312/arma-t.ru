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

<!-- Сообщение, если корзина пуста -->
<div id="empty-cart-message" class="empty-cart-m" style="display: none;">
    <p>Вы пока что ничего не добавили в корзину.</p>
</div>

<div class="checkout-block-m" id="checkout-block" style="display: none;">
    <div id="cart-items" class="cart-items-m"></div>
    <div class="total-price-m" id="total-amount"><strong>Итоговая сумма: 0 ₽</strong></div>
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
        let cartItemsContainer = document.getElementById('cart-items');
        let checkoutBlock = document.getElementById('checkout-block');
        let emptyCartMessage = document.getElementById('empty-cart-message');

        if (!cartDataString) {
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

        emptyCartMessage.style.display = "none";
        checkoutBlock.style.display = "block";

        cartItemsContainer.innerHTML = '';

        cartData.cartItems.forEach((item, index) => {
            let itemDiv = document.createElement('div');
            itemDiv.classList.add('product-checkout-m');

            let priceText = item.price && item.price !== "По запросу" ? `${item.price} ₽` : "По запросу";

            itemDiv.innerHTML = `
                <button class="remove-button-m" data-index="${index}">&times;</button>
                <div class="product-image-m">
                    <img src="${item.image || '/resources/img/production/0.png'}" alt="${item.name}">
                </div>
                <div class="product-info-m">
                    <span class="product-name-m"><strong>${item.name}</strong></span>
                    <p>Артикул: <strong>${item.article}</strong></p>
                    <p>Цена за единицу: <strong>${priceText}</strong></p>
                    <div class="quantity-control-m">
                        <button class="quantity-btn-m minus" data-index="${index}">-</button>
                        <input type="number" class="quantity-input-m" data-index="${index}" value="${item.quantity}" min="0">
                        <button class="quantity-btn-m plus" data-index="${index}">+</button>
                    </div>
                    <p>В сумме: <strong>${(parseFloat(item.price) * item.quantity).toFixed(2) || "По запросу"} ₽</strong></p>
                </div>
            `;
            itemDiv.addEventListener('click', function () {
                window.location.href = `/catalog/detail.php?ID=${item.id}`;
            });

            cartItemsContainer.appendChild(itemDiv);
        });

        document.querySelectorAll('.quantity-btn-m').forEach(button => {
            button.addEventListener('click', adjustQuantity);
        });

        document.querySelectorAll('.remove-button-m').forEach(button => {
            button.addEventListener('click', removeItem);
        });

        updateTotal(cartData.cartItems);
    }

    function updateTotal(cartItems) {
        let totalAmount = cartItems.reduce((total, item) => {
            let price = parseFloat(item.price) || 0;
            return total + (price * item.quantity);
        }, 0);

        document.getElementById('total-amount').innerText = `Итоговая сумма: ${totalAmount > 0 ? totalAmount.toFixed(2) + " ₽" : "По запросу"}`;
    }

    function adjustQuantity(event) {
        let index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));

        if (event.target.classList.contains('plus')) {
            cartData.cartItems[index].quantity++;
        } else if (event.target.classList.contains('minus')) {
            cartData.cartItems[index].quantity--;
        }

        if (cartData.cartItems[index].quantity <= 0) {
            cartData.cartItems.splice(index, 1);
        }

        localStorage.setItem('cartItems', JSON.stringify(cartData));
        loadCart();
    }

    function removeItem(event) {
        let index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));
        cartData.cartItems.splice(index, 1);
        localStorage.setItem('cartItems', JSON.stringify(cartData));
        loadCart();
    }
</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

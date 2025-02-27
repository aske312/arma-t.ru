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

<div class="checkout-m">
    <div class="section-title-m">
        <h2>Оформление заказа</h2>
        <p>Товары в корзине</p>
    </div>

    <div class="checkout-block-m">
        <div id="cart-items-m">
            <div class="product-checkout-m" id="product-checkout">
                <!-- Здесь будет вывод корзины -->
            </div>
            <div class="total-m" id="total-amount"><strong>Итоговая сумма: 0 ₽</strong></div>
        </div>
        <div class="button-group-m">
            <button onclick="history.back()" class="back-button-m">Назад</button>
            <button id="order-btn" class="order-button-m">Оформить заказ</button>
        </div>
        <div id="order-form-container" class="modal-m">
            <div class="modal-content-m">
                <span class="close-button-m" id="close-modal">&times;</span>
                <form class="order-form-m" id="order-form">
                    <h2>Ваши данные</h2>
                    <!-- Поля для ввода данных -->
                    <label for="name">Имя:</label>
                    <input type="text" id="name" name="name" required>
                    <label for="phone">Телефон:</label>
                    <input type="text" id="phone" name="phone" required>
                    <label for="company">Компания:</label>
                    <input type="text" id="company" name="company" required>
                    <label for="inn">ИНН:</label>
                    <input type="text" id="inn" name="inn" required>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <label for="address">Адрес:</label>
                    <textarea id="address" name="address" required></textarea>
                    <input type="hidden" id="cartData" name="cartData">

                    <!-- Checkbox для согласия на рассылку -->
                    <div class="newsletter-checkbox-m">
                        <label for="newsletter">
                            <input type="checkbox" id="newsletter" name="newsletter" required>
                            Согласие на рассылку
                        </label>
                    </div>
                    <button type="submit">Оформить заказ</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        loadCart();

        const orderForm = document.getElementById('order-form');
        orderForm.addEventListener('submit', function (e) {
            e.preventDefault();
            processOrder();
        });

        document.getElementById('order-btn').addEventListener('click', function () {
            document.querySelector('.modal-m').style.display = 'flex';
        });

        document.getElementById('close-modal').addEventListener('click', function () {
            document.querySelector('.modal-m').style.display = 'none';
        });
    });

    function loadCart() {
        let cartDataString = localStorage.getItem('cartItems');
        if (!cartDataString) return;

        let cartData = JSON.parse(cartDataString);
        if (!cartData || !Array.isArray(cartData.cartItems)) return;

        let cartItemsContainer = document.getElementById('product-checkout');
        cartItemsContainer.innerHTML = '';

        cartData.cartItems.forEach((item, index) => {
            let itemDiv = document.createElement('div');
            itemDiv.classList.add('cart-item');

            let imageHTML = item.image ?
                `<div class="product-image-m"><img src="${item.image}" alt="${item.name}"></div>` :
                `<div class="product-image-m"><img src="/resources/img/production/0.png" alt="Нет изображения"></div>`;

            let priceText = item.price && item.price > 0 ? `${item.price} ₽` : "под заказ";

            itemDiv.innerHTML = `
                ${imageHTML}
                <div class="product-info-m">
                    <span><strong>${item.name}</strong></span>
                    <span>Артикул: <strong>${item.article}</strong></span>
                    <span class="price-m">Цена за единицу: <strong>${priceText}</strong></span>
                    <div class="quantity-control-m">
                        <button class="quantity-btn-m minus" data-index="${index}">-</button>
                        <input type="number" value="${item.quantity}" min="1" class="quantity-input-m" data-index="${index}" />
                        <button class="quantity-btn-m plus" data-index="${index}">+</button>
                    </div>
                    <button class="remove-button-m" data-index="${index}">&times;</button>
                </div>
            `;
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

    function adjustQuantity(event) {
        const index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));

        if (event.target.classList.contains('plus')) {
            cartData.cartItems[index].quantity++;
        } else if (event.target.classList.contains('minus') && cartData.cartItems[index].quantity > 1) {
            cartData.cartItems[index].quantity--;
        }

        localStorage.setItem('cartItems', JSON.stringify(cartData));
        loadCart();
    }

    function removeItem(event) {
        const index = event.target.getAttribute('data-index');
        let cartData = JSON.parse(localStorage.getItem('cartItems'));
        cartData.cartItems.splice(index, 1);
        localStorage.setItem('cartItems', JSON.stringify(cartData));
        loadCart();
    }

</script>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>

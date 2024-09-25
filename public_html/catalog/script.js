// Корзина
let cart = [];

function toggleCart() {
    const cartDropdown = document.getElementById("cart");
    cartDropdown.classList.toggle("hidden");
}

function addToCart(itemId, itemArticul, itemPrice) {
    const existingItem = cart.find(item => item.id === itemId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ id: itemId, articul: itemArticul, price: itemPrice, quantity: 1 });
    }
    updateCart();
}

function updateCart() {
    const cartCount = document.getElementById("cart-count");
    const cartItems = document.getElementById("cart-items");
    const cartTotalPrice = document.getElementById("cart-total-price");

    cartCount.innerText = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartItems.innerHTML = cart.map(item => `
        <li>
            ${item.articul} — ${item.quantity} шт. — ${item.price * item.quantity} руб.
            <button onclick="changeQuantity(${item.id}, -1)">-</button>
            <button onclick="changeQuantity(${item.id}, 1)">+</button>
        </li>
    `).join('');

    cartTotalPrice.innerText = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

function changeQuantity(itemId, delta) {
    const item = cart.find(item => item.id === itemId);
    if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) {
            cart = cart.filter(item => item.id !== itemId);
        }
        updateCart();
    }
}

function clearCart() {
    cart = [];
    updateCart();
}

function checkout() {
    alert("Оформление заказа");
    // здесь будет логика для оформления заказа
}

function addSelectedToCart() {
    const checkboxes = document.querySelectorAll(".catalog-item-checkbox:checked");
    checkboxes.forEach(checkbox => {
        const itemId = checkbox.getAttribute("data-id");
        const itemArticul = checkbox.getAttribute("data-articul");
        const itemPrice = parseFloat(checkbox.getAttribute("data-price"));
        addToCart(itemId, itemArticul, itemPrice);
    });
}

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll(".catalog-item-checkbox");
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
}
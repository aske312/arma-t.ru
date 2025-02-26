function toggleMenu() {
    const nav = document.getElementById('mobileMenu');
    nav.classList.toggle('open');
}

window.addEventListener('scroll', function() {
    document.getElementById('siteHeader').classList.toggle('fixed', window.scrollY > 100);
});

function getCartItems() {
    const storedData = JSON.parse(localStorage.getItem('cartItems'));
    return storedData && storedData.cartItems ? storedData.cartItems : [];
}

function setCartItems(cartItems) {
    const expiryDate = Date.now() + 3 * 24 * 60 * 60 * 1000;
    const cartData = { cartItems, expiry: expiryDate };
    localStorage.setItem('cartItems', JSON.stringify(cartData));
}

function updateCartCount() {
    const cartItems = getCartItems();
    const count = cartItems.reduce((total, item) => total + item.quantity, 0);
    document.getElementById('cart-count').textContent = count;
}

// *** Поиск ***//
document.getElementById('search').addEventListener('input', function() {
    const query = this.value;

    // Если строка пустая, убираем предложения
    if (!query) {
        document.getElementById('suggestions').innerHTML = '';
        document.getElementById('suggestions').style.display = 'none';
        return;
    }

    // Отправляем запрос на сервер для получения предложений
    fetch(`/resources/src/search_suggestions.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const suggestionsDiv = document.getElementById('suggestions');
            suggestionsDiv.innerHTML = ''; // Очищаем текущие предложения

            // Если есть предложения
            if (data.length > 0) {
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.textContent = item.name;
                    div.classList.add('suggestion-item');  // Добавляем класс для стилей
                    div.onclick = () => window.location.href = item.url;  // Перенаправление при клике
                    suggestionsDiv.appendChild(div);
                });
                // Показываем блок с предложениями
                suggestionsDiv.style.display = 'block';
            } else {
                suggestionsDiv.innerHTML = 'Ничего не найдено';
                suggestionsDiv.style.display = 'block';
            }
        })
        .catch(error => console.error('Error fetching search suggestions:', error));
});

// Открытие формы
function openForm() {
    document.getElementById('Form').classList.add('active');
}

// Закрытие формы (можно добавлять по кнопке или кликом вне формы)
function closeForm() {
    document.getElementById('Form').classList.remove('active');
}

// Закрытие формы при клике вне области формы
window.onclick = function(event) {
    if (event.target === document.getElementById('Form')) {
        closeForm();
    }
}

function loadCartData() {
    const cartItems = getCartItems();
    const cartItemsContainer = document.getElementById('cart-items');
    cartItemsContainer.innerHTML = '';
    let totalSum = 0;

    if (cartItems.length === 0) {
        cartItemsContainer.innerHTML = '<p>Корзина пуста</p>';
        document.getElementById('cart-total').innerText = 'Общая сумма: Под заказ';
        return;
    }

    cartItems.forEach(async (item) => {
        const itemTotal = (item.price * item.quantity).toFixed(2);
        totalSum += parseFloat(itemTotal); // Считаем общую сумму здесь

        // Если у товара нет изображения, запрашиваем изображение раздела
        let imageUrl = item.image || '/resources/img/production/0.png';
        if (!item.image) {
            imageUrl = await fetchSectionImage(item.id);
        }

        const cartItemHTML = `
            <div class="cart-item-card">
                <a href="/catalog/detail.php?ID=${item.id}" class="cart-item-link">
                    <img src="${imageUrl}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <p class="cart-item-name">${item.name}</p>
                        <p class="cart-item-article">Артикул: ${item.article}</p>
                        <p class="cart-item-price">
                            ${item.price && item.price > 0 ? item.price + ' руб./шт.' : 'Цена под заказ'}
                        </p>
                    </div>
                </a>
                    <div class="cart-item-actions">
                        <button class="quantity-btn" onclick="updateQuantity('${item.id}', -1)">&#8722;</button>
                        <input type="number" class="quantity-input" value="${item.quantity}" onchange="updateQuantityManual('${item.id}', this.value)">
                        <button class="quantity-btn" onclick="updateQuantity('${item.id}', 1)">&#43;</button>
                        <span class="cart-item-total">
                            ${!isNaN(itemTotal) && itemTotal !== null && itemTotal > 0 ? itemTotal + ' руб.' : ''}
                        </span>
                        <span class="remove-item-btn" onclick="removeCartItem('${item.id}')">&#10005;</span>
                    </div>
            </div>
        `;

        cartItemsContainer.innerHTML += cartItemHTML;
    });

    // Проверяем общую сумму и выводим "Под заказ", если сумма равна 0
    document.getElementById('cart-total').innerText = totalSum > 0
        ? `Общая сумма: ${totalSum.toFixed(2)} руб.`
        : 'Общая сумма: под заказ';
}

// Функция для получения изображения раздела
async function fetchSectionImage(itemId) {
    try {
        const response = await fetch(`/getSectionImage.php?itemId=${itemId}`);
        const data = await response.json();
        return data.imageUrl;
    } catch (error) {
        console.error('Ошибка при получении изображения раздела:', error);
        return '/resources/img/production/0.png';
    }
}

function updateQuantity(productId, delta) {
    const cartItems = getCartItems();
    const item = cartItems.find(item => item.id === productId);

    if (item) {
        item.quantity += delta;
        if (item.quantity < 1) {
            removeCartItem(productId);
        } else {
            setCartItems(cartItems);
            loadCartData();
        }
    }
}

function updateQuantityManual(productId, value) {
    const cartItems = getCartItems();
    const item = cartItems.find(item => item.id === productId);

    if (item) {
        item.quantity = Math.max(1, parseInt(value) || 1);
        setCartItems(cartItems);
        loadCartData();
    }
}

function removeCartItem(productId) {
    let cartItems = getCartItems();
    cartItems = cartItems.filter(item => item.id !== productId);
    setCartItems(cartItems);
    loadCartData();
}

document.getElementById('clear-cart').addEventListener('click', function() {
    localStorage.removeItem('cartItems');
    loadCartData();
    updateCartCount();
});

updateCartCount();

document.getElementById('checkout').addEventListener('click', function() {
    window.location.href = '/checkout';
});

document.getElementById('close-cart-modal').addEventListener('click', function() {
    document.getElementById('cart-modal').style.display = 'none';
});

document.getElementById('cart-modal-overlay').addEventListener('click', function() {
    document.getElementById('cart-modal').style.display = 'none';
});

// Закрытие модального окна при клике вне формы
window.onclick = function(event) {
    if (event.target === document.getElementById('Form')) {
        document.getElementById('Form').classList.remove('active');
    }
};

// Закрытие формы по кнопке
document.querySelector('.close-form-header').addEventListener('click', function() {
    document.getElementById('Form').classList.remove('active');
});

document.getElementById('contactForm-header').addEventListener('submit', function (e) {
    e.preventDefault();  // Предотвращаем перезагрузку страницы

    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true; // Блокируем кнопку

    const formData = new FormData(this);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/resources/src/send.php', true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('contactForm-header').reset();
            alert('Ваше сообщение было успешно отправлено!');
            setTimeout(() => {
                submitButton.disabled = false;
            }, 1800); // 180000 мс = 3 минуты
        } else {
            alert('Произошла ошибка при отправке сообщения.');
            submitButton.disabled = false;
        }
    };

    xhr.onerror = function () {
        alert('Произошла ошибка при отправке сообщения.');
        submitButton.disabled = false;
    };

    xhr.send(formData);
});

// Функция для изменения размеров окна корзины
function setupCartModalResize() {
    const cartModalContent = document.querySelector('.cart-modal-content');
    const resizeHandle = document.createElement('div');
    resizeHandle.className = 'resize-handle';
    cartModalContent.appendChild(resizeHandle);

    let isResizing = false;

    resizeHandle.addEventListener('mousedown', (e) => {
        e.preventDefault();
        isResizing = true;
        document.addEventListener('mousemove', resizeModal);
        document.addEventListener('mouseup', stopResize);
    });

    function resizeModal(e) {
        if (isResizing) {
            const newWidth = e.clientX - cartModalContent.getBoundingClientRect().left;
            const newHeight = e.clientY - cartModalContent.getBoundingClientRect().top;

            if (newWidth > 400 && newWidth < 800) {
                cartModalContent.style.width = `${newWidth}px`;
            }
            if (newHeight > 400 && newHeight < 600) {
                cartModalContent.style.height = `${newHeight}px`;
            }
        }
    }

    function stopResize() {
        isResizing = false;
        document.removeEventListener('mousemove', resizeModal);
        document.removeEventListener('mouseup', stopResize);
    }
}

// Функция для перетаскивания окна корзины
function setupCartModalDrag() {
    const cartModalContent = document.querySelector('.cart-modal-content');
    const cartHeader = cartModalContent.querySelector('h2');

    let isDragging = false;
    let offsetX, offsetY;

    cartHeader.addEventListener('mousedown', (e) => {
        isDragging = true;
        offsetX = e.clientX - cartModalContent.getBoundingClientRect().left;
        offsetY = e.clientY - cartModalContent.getBoundingClientRect().top;
        document.addEventListener('mousemove', dragModal);
        document.addEventListener('mouseup', stopDrag);
    });

    function dragModal(e) {
        if (isDragging) {
            cartModalContent.style.left = `${e.clientX - offsetX}px`;
            cartModalContent.style.top = `${e.clientY - offsetY}px`;
        }
    }

    function stopDrag() {
        isDragging = false;
        document.removeEventListener('mousemove', dragModal);
        document.removeEventListener('mouseup', stopDrag);
    }
}

// Инициализация функций при открытии корзины
document.getElementById('cart-button').addEventListener('click', function() {
    const cartModal = document.getElementById('cart-modal');
    cartModal.style.display = cartModal.style.display === 'block' ? 'none' : 'block';
    loadCartData();
    setupCartModalResize();
});

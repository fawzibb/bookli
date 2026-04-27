<script>
let cart = [];

function toggleCart(){
    document.querySelector('.cart')?.classList.toggle('show');
}

function addToCart(id, name, price){
    const existing = cart.find(item => item.id === id);

    if(existing){
        existing.quantity += 1;
    }else{
        cart.push({
            id:id,
            name:name,
            price:Number(price),
            quantity:1
        });
    }

    renderCart();
}

function increaseItem(index){
    cart[index].quantity += 1;
    renderCart();
}

function decreaseItem(index){
    cart[index].quantity -= 1;

    if(cart[index].quantity <= 0){
        cart.splice(index, 1);
    }

    renderCart();
}

function removeItem(index){
    cart.splice(index, 1);
    renderCart();
}

function renderCart(){
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const mobileCartTotal = document.getElementById('mobile-cart-total');
    const itemsInput = document.getElementById('items-input');

    cartItems.innerHTML = '';

    let total = 0;

    if(cart.length === 0){
        cartItems.innerHTML =
            '<div class="cart-empty">{{ __("messages.cart_empty") }}</div>';

        cartTotal.textContent = '0.00';

        if(mobileCartTotal){
            mobileCartTotal.textContent = '0.00';
        }

        itemsInput.value = '';
        return;
    }

    cart.forEach((item, index) => {
        total += item.price * item.quantity;

        const div = document.createElement('div');
        div.className = 'cart-item';

        div.innerHTML = `
            <div class="cart-item-top">
                <div>
                    <div class="cart-item-name">${item.name}</div>
                    <div class="qty">{{ __("messages.qty") }}: ${item.quantity}</div>
                </div>

                <div class="cart-item-price">
                    $${(item.price * item.quantity).toFixed(2)}
                </div>
            </div>

            <div class="cart-item-actions">
                <button type="button" class="qty-btn" onclick="decreaseItem(${index})">-</button>
                <button type="button" class="qty-btn" onclick="increaseItem(${index})">+</button>
                <button type="button" class="remove-btn" onclick="removeItem(${index})">×</button>
            </div>
        `;

        cartItems.appendChild(div);
    });

    cartTotal.textContent = total.toFixed(2);

    if(mobileCartTotal){
        mobileCartTotal.textContent = total.toFixed(2);
    }

    itemsInput.value = JSON.stringify(cart);
}

document.getElementById('order-form')?.addEventListener('submit', function(e){
    if(cart.length === 0){
        e.preventDefault();
        alert('{{ __("messages.cart_empty_alert") }}');
    }
});
</script>
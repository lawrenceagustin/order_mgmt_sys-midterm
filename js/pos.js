// js/pos.js
document.addEventListener('DOMContentLoaded', function() {
  let cart = [];

  const cartItemsContainer = document.getElementById('cart-items');
  const totalElement = document.getElementById('total');
  const paymentInput = document.getElementById('payment');
  const checkoutButton = document.getElementById('checkout');

  // expose addToCart globally for index preview buttons
  window.addToCart = function(item) {
    const existingItem = cart.find(i => i.product_id == item.product_id);
    if (existingItem) existingItem.quantity += 1;
    else cart.push({ ...item, quantity: 1 });
    updateCartDisplay();
  };

  function updateCartDisplay() {
    cartItemsContainer.innerHTML = '';
    cart.forEach(item => {
      const itemElement = document.createElement('div');
      itemElement.className = 'flex justify-between items-center mb-2 p-2 bg-gray-100 rounded';
      itemElement.innerHTML = `
        <div class="flex-1">
          <div class="font-medium">${item.name}</div>
          <div class="text-sm">₱${parseFloat(item.price).toFixed(2)} x ${item.quantity}</div>
        </div>
        <div class="flex items-center">
          <button class="dec px-2 bg-gray-200 rounded" data-id="${item.product_id}">-</button>
          <span class="mx-2">${item.quantity}</span>
          <button class="inc px-2 bg-gray-200 rounded" data-id="${item.product_id}">+</button>
          <button class="rm px-2 ml-2 text-red-600" data-id="${item.product_id}">×</button>
        </div>
      `;
      cartItemsContainer.appendChild(itemElement);
    });

    document.querySelectorAll('.dec').forEach(b => {
      b.addEventListener('click', function() {
        const id = this.dataset.id;
        const it = cart.find(x => x.product_id == id);
        if (!it) return;
        if (it.quantity > 1) it.quantity -= 1;
        else cart = cart.filter(x => x.product_id != id);
        updateCartDisplay();
      });
    });

    document.querySelectorAll('.inc').forEach(b => {
      b.addEventListener('click', function() {
        const id = this.dataset.id;
        const it = cart.find(x => x.product_id == id);
        if (it) it.quantity += 1;
        updateCartDisplay();
      });
    });

    document.querySelectorAll('.rm').forEach(b => {
      b.addEventListener('click', function() {
        const id = this.dataset.id;
        cart = cart.filter(x => x.product_id != id);
        updateCartDisplay();
      });
    });

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    totalElement.textContent = `₱${total.toFixed(2)}`;
  }

  checkoutButton.addEventListener('click', async function() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const payment = parseFloat(paymentInput.value) || 0;
    const customerName = (document.getElementById('customer_name') ? document.getElementById('customer_name').value.trim() : '') || 'Guest';

    if (cart.length === 0) {
      Swal.fire('Cart is empty','Add items first','error'); return;
    }
    if (payment < total) {
      Swal.fire('Insufficient payment', `Total is ₱${total.toFixed(2)}`, 'error'); return;
    }

    const items = cart.map(i => ({ product_id: i.product_id, quantity: i.quantity, subtotal: i.price * i.quantity }));

    try {
      const res = await fetch('api.php?action=place_order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ items, total, customer_name: customerName })
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Order failed');
      const change = payment - total;
      Swal.fire('Order successful', `Order #${data.order_number}\nChange: ₱${change.toFixed(2)}`, 'success');
      cart = [];
      paymentInput.value = '';
      updateCartDisplay();
    } catch (err) {
      Swal.fire('Error', err.message, 'error');
    }
  });

  // smooth anchor
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
    });
  });
});

<?php
session_start();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>SuiSway - Order</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-[url('uploads/bg-3.jpg')] bg-cover bg-fixed">
  <nav class="bg-white fixed w-full h-20 z-50 top-0 border-b">
    <div class="max-w-6xl mx-auto flex items-center justify-between p-4 h-full">
      <a href="index.php"><img src="uploads/suisway-logo.png" class="h-20"></a>
      <div class="space-x-3">
        <a href="admin_login.php" class="text-sm text-gray-700">Staff Login</a>
      </div>
    </div>
  </nav>

  <main class="pt-24 max-w-6xl mx-auto p-4">
    <section class="grid md:grid-cols-2 gap-6">
      <div class="bg-white/90 p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-2">Welcome to SuiSway</h2>
        <p class="mb-4">Order from our menu below. Fill Customer name if you want receipt with name.</p>

        <div class="mb-4">
          <label class="block mb-1 font-medium">Customer name (optional)</label>
          <input id="customer_name" class="w-full border p-2 rounded" placeholder="e.g. Juan Dela Cruz">
        </div>

        <div id="menuPreview" class="grid grid-cols-2 gap-3"></div>
      </div>

      <div class="bg-white/90 p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-3">Cart</h2>
        <div id="cart-items" class="mb-4 max-h-96 overflow-auto"></div>
        <div class="flex justify-between items-center mb-4">
          <div>Total:</div>
          <div id="total" class="font-bold">₱0.00</div>
        </div>

        <div class="mb-3">
          <label>Payment</label>
          <div class="flex items-center">
            <span class="px-2">₱</span>
            <input id="payment" type="number" step="0.01" min="0" class="border p-2 rounded w-full">
          </div>
        </div>
        <button id="checkout" class="w-full bg-green-600 text-white py-2 rounded">Checkout</button>
      </div>
    </section>
  </main>

<script src="js/pos.js"></script>
<script>
async function loadPreview(){
  const res = await fetch('api.php?action=get_products');
  const data = await res.json();
  const container = document.getElementById('menuPreview');
  container.innerHTML = '';
  data.products.forEach(p=>{
    const el = document.createElement('div');
    el.className = 'border rounded p-2';
    el.innerHTML = `
      <div class="h-28 overflow-hidden mb-2"><img src="${p.image ? p.image : 'uploads/no-image.png'}" class="w-full h-full object-cover"></div>
      <div class="font-semibold">${p.name}</div>
      <div>₱${parseFloat(p.price).toFixed(2)}</div>
      <button class="mt-2 add-btn bg-blue-600 text-white px-2 py-1 rounded" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}">Add</button>
    `;
    container.appendChild(el);
  });

  document.querySelectorAll('.add-btn').forEach(b=>{
    b.addEventListener('click', function(){
      const id = this.dataset.id;
      const name = this.dataset.name;
      const price = parseFloat(this.dataset.price);
      window.addToCart({ product_id: id, name, price });
    });
  });
}

loadPreview();
</script>
</body>
</html>

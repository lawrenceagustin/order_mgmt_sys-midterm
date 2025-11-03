<?php
require_once 'db_config.php';
if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','superadmin'])) {
    header('Location: admin_login.php'); exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>POS - Staff</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 p-4">
  <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="md:col-span-2 bg-white p-4 rounded shadow">
      <h2 class="text-xl font-bold mb-3">Menu</h2>
      <div id="menuGrid" class="grid grid-cols-2 md:grid-cols-3 gap-4"></div>
    </div>

    <div class="bg-white p-4 rounded shadow">
      <h2 class="text-xl mb-3">Cart</h2>
      <div id="cartItems" class="mb-4"></div>
      <div class="mb-2">Total: <span id="cartTotal">₱0.00</span></div>
      <label>Customer name</label><input id="cust_name" class="w-full border p-2 mb-2">
      <button id="checkoutBtn" class="w-full bg-blue-600 text-white p-2 rounded">Process Order</button>
      <div class="mt-2"><a href="admin_home.php">Back</a> | <a href="logout.php">Logout</a></div>
    </div>
  </div>

<script src="js/pos.js"></script>
<script>
const pos = {
  cart: [],
  add(item){ const ex = this.cart.find(i=>i.product_id==item.product_id); if(ex) ex.quantity++; else this.cart.push({...item, quantity:1}); render(); },
  remove(id){ this.cart = this.cart.filter(i=>i.product_id!=id); render(); },
  changeQty(id,q){ const it = this.cart.find(i=>i.product_id==id); if(!it) return; it.quantity = q; if(it.quantity<=0) this.remove(id); render(); },
  clear(){ this.cart = []; render(); }
};

async function loadMenu(){
  const res = await fetch('api.php?action=get_products');
  const data = await res.json();
  const grid = document.getElementById('menuGrid');
  grid.innerHTML = '';
  data.products.forEach(p=>{
    const div = document.createElement('div'); div.className='border rounded p-2';
    div.innerHTML = `<div class="h-28 overflow-hidden mb-2"><img src="${p.image ? p.image : 'uploads/no-image.png'}" class="w-full h-full object-cover"></div>
      <div class="font-semibold">${p.name}</div><div>₱${parseFloat(p.price).toFixed(2)}</div>
      <button class="mt-2 addBtn bg-green-500 text-white px-3 py-1 rounded" data-id="${p.id}" data-name="${p.name}" data-price="${p.price}">Add</button>`;
    grid.appendChild(div);
  });
  document.querySelectorAll('.addBtn').forEach(btn=>{
    btn.addEventListener('click', ()=> pos.add({ product_id: btn.dataset.id, name: btn.dataset.name, price: parseFloat(btn.dataset.price) }));
  });
}

function render(){
  const container = document.getElementById('cartItems'); container.innerHTML = '';
  pos.cart.forEach(it=>{
    const el = document.createElement('div'); el.className='flex justify-between items-center mb-2';
    el.innerHTML = `<div><div class="font-semibold">${it.name}</div><div class="text-sm">₱${parseFloat(it.price).toFixed(2)} x ${it.quantity}</div></div>
      <div><button class="px-2 dec" data-id="${it.product_id}">-</button><button class="px-2 inc" data-id="${it.product_id}">+</button><button class="px-2 text-red-600 rm" data-id="${it.product_id}">x</button></div>`;
    container.appendChild(el);
  });
  document.querySelectorAll('.dec').forEach(b=>b.addEventListener('click', ()=>{ const id=b.dataset.id; const it=pos.cart.find(i=>i.product_id==id); if(it) pos.changeQty(id, it.quantity-1); }));
  document.querySelectorAll('.inc').forEach(b=>b.addEventListener('click', ()=>{ const id=b.dataset.id; const it=pos.cart.find(i=>i.product_id==id); if(it) pos.changeQty(id, it.quantity+1); }));
  document.querySelectorAll('.rm').forEach(b=>b.addEventListener('click', ()=>{ pos.remove(b.dataset.id); }));
  const total = pos.cart.reduce((s,i)=>s + (i.price * i.quantity),0);
  document.getElementById('cartTotal').textContent = '₱' + total.toFixed(2);
}

document.getElementById('checkoutBtn').addEventListener('click', async ()=>{
  if (pos.cart.length === 0) return Swal.fire('Cart empty','Add items','error');
  const total = pos.cart.reduce((s,i)=>s + (i.price * i.quantity),0);
  const customer_name = document.getElementById('cust_name').value.trim() || null;
  const items = pos.cart.map(i=>({ product_id: i.product_id, quantity: i.quantity, subtotal: i.price * i.quantity }));
  try {
    const res = await fetch('api.php?action=place_order', {
      method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ items, total, customer_name })
    });
    const d = await res.json();
    if (!res.ok) throw new Error(d.error || 'Failed');
    Swal.fire('Saved','Order #: '+d.order_number,'success');
    pos.clear();
    document.getElementById('cust_name').value='';
  } catch (err) { Swal.fire('Error', err.message, 'error'); }
});

loadMenu();
render();
</script>
</body>
</html>

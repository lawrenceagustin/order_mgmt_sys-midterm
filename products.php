<?php
require_once 'db_config.php';
if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','superadmin'])) {
    header('Location: admin_login.php');
    exit;
}

$dashboard = ($_SESSION['user']['role'] === 'superadmin') 
    ? 'superadmin_home.php' 
    : 'admin_home.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Products</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: "#454A93",
        accent: "#7BACEC"
      }
    }
  }
}
</script>
</head>

<body class="min-h-screen bg-gray-100">

<header class="w-full bg-gradient-to-r from-primary to-accent text-white py-6 shadow-lg">
  <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
    <h1 class="text-3xl font-bold tracking-wide">Products Management</h1>

    <nav class="flex gap-2">
      <a href="<?= $dashboard ?>"
         class="px-4 py-2 bg-white text-primary font-semibold rounded-lg shadow hover:bg-accent hover:text-white transition">
        Dashboard
      </a>

      <a href="logout.php"
         class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow hover:bg-red-700 transition">
        Logout
      </a>
    </nav>
  </div>
</header>

<main class="max-w-6xl mx-auto px-4 mt-10">
  <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 mb-8">
    <h2 class="text-2xl font-semibold text-primary mb-4">Add New Product</h2>

    <form id="productForm" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <input type="text" name="name" id="p_name" placeholder="Product Name"
             class="border p-3 rounded-lg focus:ring-2 focus:ring-accent focus:outline-none">

      <input type="number" name="price" id="p_price" placeholder="Price" step="0.01"
             class="border p-3 rounded-lg focus:ring-2 focus:ring-accent focus:outline-none">

      <input type="file" name="image" id="p_image" accept="image/*"
             class="border p-2 rounded-lg bg-gray-50">

      <button class="md:col-span-3 mt-2 bg-primary hover:bg-accent transition text-white py-3 rounded-lg shadow">
        Add Product
      </button>
    </form>
  </div>

  <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6">
    <h2 class="text-2xl font-semibold text-primary mb-4">Current Products</h2>

    <div id="productsList" class="grid grid-cols-1 md:grid-cols-3 gap-6"></div>
  </div>

</main>

<script>
async function loadProducts() {
  const res = await fetch('api.php?action=get_products');
  const data = await res.json();
  if (!res.ok) return Swal.fire('Error', data.error || 'Failed', 'error');

  const cl = document.getElementById('productsList');
  cl.innerHTML = '';

  data.products.forEach(p => {
    const el = document.createElement('div');
    el.className = 'rounded-xl shadow-lg border bg-white overflow-hidden hover:shadow-2xl transition';

    el.innerHTML = `
      <div class="h-40 bg-gray-100 overflow-hidden">
        <img src="${p.image ? p.image : 'uploads/no-image.png'}" class="w-full h-full object-cover">
      </div>

      <div class="p-4">
        <div class="font-semibold text-lg text-primary">${p.name}</div>
        <div class="text-gray-700 mb-1">₱${parseFloat(p.price).toFixed(2)}</div>
        <div class="text-xs text-gray-500 mb-4">Added by: ${p.added_by_username ?? 'N/A'}</div>

        <button data-id="${p.id}"
          class="deleteProduct w-full bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition">
          Delete Product
        </button>
      </div>
    `;

    cl.appendChild(el);
  });

  document.querySelectorAll('.deleteProduct').forEach(btn => {
    btn.addEventListener('click', async () => {

      const confirm = await Swal.fire({
        title: "Delete this product?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it"
      });

      if (!confirm.isConfirmed) return;

      const id = btn.dataset.id;

      const res = await fetch('api.php?action=delete_product', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ product_id: id })
      });

      const d = await res.json();
      if (!res.ok) return Swal.fire('Error', d.error || 'Failed', 'error');

      Swal.fire('Deleted', 'Product has been removed.', 'success');
      loadProducts();
    });
  });
}

document.getElementById('productForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  fd.append('action', 'add_product');

  const res = await fetch('api.php?action=add_product', {
    method: 'POST',
    body: fd
  });

  const data = await res.json();
  if (!res.ok) return Swal.fire('Error', data.error || 'Failed', 'error');

  Swal.fire('Success', 'Product added successfully', 'success');

  this.reset();
  loadProducts();
});

loadProducts();
</script>

</body>
</html>

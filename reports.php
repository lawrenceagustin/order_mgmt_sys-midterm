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
<title>Reports</title>
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

<body class="bg-gray-100 min-h-screen">
<header class="w-full bg-gradient-to-r from-primary to-accent text-white py-6 shadow-lg">
  <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
    <h1 class="text-3xl font-bold tracking-wide">Orders Report</h1>

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

<main class="max-w-6xl mx-auto px-4 mt-10 bg-white p-6 rounded-2xl shadow-xl border border-gray-200">
  <div class="flex flex-col md:flex-row gap-3 mb-6">
    <input id="start" type="date" class="border p-3 rounded-lg w-full focus:ring-2 focus:ring-accent">

    <input id="end" type="date" class="border p-3 rounded-lg w-full focus:ring-2 focus:ring-accent">

    <button id="filterBtn" 
            class="bg-primary text-white px-4 py-3 rounded-lg shadow hover:bg-accent transition w-full md:w-auto">
      Filter
    </button>

    <button id="printBtn"
            class="bg-gray-700 text-white px-4 py-3 rounded-lg shadow hover:bg-black transition w-full md:w-auto">
      Print / PDF
    </button>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
      <thead class="bg-primary text-white">
        <tr>
          <th class="p-3 border">Order#</th>
          <th class="p-3 border">Date</th>
          <th class="p-3 border">Customer</th>
          <th class="p-3 border">Items</th>
          <th class="p-3 border">Total</th>
        </tr>
      </thead>

      <tbody id="reportBody" class="bg-white"></tbody>

      <tfoot>
        <tr class="bg-accent text-white font-bold">
          <td colspan="4" class="p-3 text-right">Grand Total</td>
          <td id="grandTotal" class="p-3">₱0.00</td>
        </tr>
      </tfoot>
    </table>
  </div>

</main>

<script>
async function loadReports(){
  const start = document.getElementById('start').value || null;
  const end = document.getElementById('end').value || null;

  const res = await fetch('api.php?action=get_orders', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ date_start: start, date_end: end })
  });

  const d = await res.json();
  if (!res.ok) return Swal.fire('Error', d.error || 'Failed', 'error');

  const tbody = document.getElementById('reportBody');
  tbody.innerHTML = '';

  let grandTotal = 0;

  d.orders.forEach(o=>{
    const row = document.createElement('tr');
    row.className = "hover:bg-gray-100 transition";

    row.innerHTML = `
      <td class="border p-3">${o.order_number}</td>
      <td class="border p-3">${o.date_added}</td>
      <td class="border p-3">${o.customer_name ?? ''}</td>
      <td class="border p-3 text-sm leading-5">${o.items.map(it => `${it.name} x${it.quantity}`).join('<br>')}</td>
      <td class="border p-3">₱${parseFloat(o.total).toFixed(2)}</td>
    `;

    tbody.appendChild(row);
    grandTotal += parseFloat(o.total);
  });

  document.getElementById('grandTotal').textContent = '₱' + grandTotal.toFixed(2);
}

document.getElementById('filterBtn').addEventListener('click', loadReports);

document.getElementById('printBtn').addEventListener('click', function(){
  const s = document.getElementById('start').value || '';
  const e = document.getElementById('end').value || '';
  window.open('report_print.php?start=' + encodeURIComponent(s) + '&end=' + encodeURIComponent(e), '_blank');
});
loadReports();
</script>

</body>
</html>

<?php
require_once 'db_config.php';
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    header('Location: admin_login.php'); exit;
}
?>
<!doctype html>
<html>
<head>
 <meta charset="utf-8">
 <title>Superadmin Dashboard</title>
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
<nav class="bg-primary text-white shadow-lg px-6 py-4">
  <div class="max-w-6xl mx-auto flex justify-between items-center">
    <h1 class="text-2xl font-bold tracking-wide">Superadmin Dashboard</h1>

    <div class="flex gap-3">
      <a href="products.php" class="px-4 py-2 bg-accent text-primary font-semibold rounded hover:opacity-90 transition">Products</a>
      <a href="reports.php" class="px-4 py-2 bg-accent text-primary font-semibold rounded hover:opacity-90 transition">Reports</a>
      <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded font-semibold hover:bg-red-600 transition">Logout</a>
    </div>
  </div>
</nav>

<div class="max-w-6xl mx-auto p-6">

  <div class="bg-white p-6 rounded-xl shadow mb-6 border border-gray-200">
    <h2 class="text-xl font-bold text-primary mb-4">Create Admin (Cashier)</h2>

    <form id="createAdminForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">

      <input id="ca_user" placeholder="Username" 
             class="border border-gray-300 p-2 rounded focus:ring-accent focus:border-accent">

      <input id="ca_first" placeholder="First name" 
             class="border border-gray-300 p-2 rounded focus:ring-accent focus:border-accent">

      <input id="ca_last" placeholder="Last name" 
             class="border border-gray-300 p-2 rounded focus:ring-accent focus:border-accent">

      <input id="ca_pass" type="password" placeholder="Password" 
             class="border border-gray-300 p-2 rounded focus:ring-accent focus:border-accent">

      <button class="md:col-span-4 mt-2 bg-primary text-white py-2 rounded-lg hover:bg-accent hover:text-primary font-semibold transition">
        Create Admin
      </button>
    </form>
  </div>

  <div class="bg-white p-6 rounded-xl shadow border border-gray-200 mb-10">
    <h2 class="text-xl font-bold text-primary mb-4">All Users</h2>

    <div id="usersList" class="divide-y divide-gray-200"></div>
  </div>
</div>

<script>
async function loadUsers(){
  const res = await fetch('api.php?action=get_users');
  const data = await res.json();
  if (!res.ok) return Swal.fire('Error', data.error || 'Failed', 'error');

  const container = document.getElementById('usersList');
  container.innerHTML = '';

  data.users.forEach(u => {
    const div = document.createElement('div');
    div.className = "flex justify-between items-center py-3";

    div.innerHTML = `
      <div>
        <strong class="text-primary">${u.username}</strong> 
        <span class="text-gray-700">- ${u.firstname} ${u.lastname}</span>
        <span class="text-sm text-gray-500">(${u.role})</span>
      </div>

      <div>
        ${
          u.status === 'active' 
          ? `<button class="suspendBtn bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition" data-id="${u.id}">Suspend</button>`
          : `<button class="reactivateBtn bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition" data-id="${u.id}">Reactivate</button>`
        }
      </div>
    `;
    container.appendChild(div);
  });

  document.querySelectorAll('.suspendBtn').forEach(b=>{
    b.addEventListener('click', async ()=> {
      const id = b.dataset.id;
      const res = await fetch('api.php?action=suspend_user', {
        method:'POST', 
        headers:{'Content-Type':'application/json'}, 
        body: JSON.stringify({user_id: id})
      });
      const d = await res.json();
      if (!res.ok) return Swal.fire('Error', d.error || 'Failed', 'error');
      Swal.fire('Suspended','User suspended','success').then(loadUsers);
    });
  });

  document.querySelectorAll('.reactivateBtn').forEach(b=>{
    b.addEventListener('click', async ()=> {
      const id = b.dataset.id;
      const res = await fetch('api.php?action=reactivate_user', {
        method:'POST', 
        headers:{'Content-Type':'application/json'}, 
        body: JSON.stringify({user_id: id})
      });
      const d = await res.json();
      if (!res.ok) return Swal.fire('Error', d.error || 'Failed', 'error');
      Swal.fire('Reactivated','User reactivated','success').then(loadUsers);
    });
  });
}

document.getElementById('createAdminForm').addEventListener('submit', async function(e){
  e.preventDefault();

  const username = document.getElementById('ca_user').value.trim();
  const firstname = document.getElementById('ca_first').value.trim();
  const lastname = document.getElementById('ca_last').value.trim();
  const password = document.getElementById('ca_pass').value;

  if (!username || !firstname || !lastname || !password) {
    return Swal.fire('Validation','All fields required','error');
  }

  const res = await fetch('api.php?action=create_admin', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({username, firstname, lastname, password})
  });

  const d = await res.json();

  if (!res.ok) return Swal.fire('Error', d.error || 'Failed', 'error');

  Swal.fire('Success','Admin created successfully','success');
  this.reset();
  loadUsers();
});

loadUsers();
</script>

</body>
</html>

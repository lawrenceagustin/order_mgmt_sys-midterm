<?php
require_once 'db_config.php';
if (!empty($_SESSION['user']) && in_array($_SESSION['user']['role'], ['admin','superadmin'])) {
    if ($_SESSION['user']['role'] === 'superadmin') header('Location: superadmin_home.php');
    else header('Location: admin_home.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Staff Login</title>
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

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary to-accent p-6">

  <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-xl border border-gray-200">
    <h2 class="text-2xl font-bold text-primary mb-6 text-center tracking-wide">
      Staff Login
    </h2>
    <form id="loginForm" class="space-y-4">
      <input id="user" 
             placeholder="Username"
             class="w-full border border-gray-300 p-3 rounded-lg focus:ring-accent focus:border-accent outline-none transition">
      <input id="pass" type="password"
             placeholder="Password"
             class="w-full border border-gray-300 p-3 rounded-lg focus:ring-accent focus:border-accent outline-none transition">
      <button class="w-full bg-primary text-white p-3 rounded-lg font-semibold hover:bg-accent hover:text-primary transition-all duration-300 shadow">
        Login
      </button>
    </form>

  </div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const username = document.getElementById('user').value.trim();
  const password = document.getElementById('pass').value;

  if (!username || !password) {
    return Swal.fire('Validation Error', 'Please enter both fields.', 'error');
  }
  const res = await fetch('api.php?action=admin_login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  });
  const data = await res.json();
  if (!res.ok) {
    return Swal.fire('Login Failed', data.error || 'Invalid credentials', 'error');
  }
  Swal.fire({
    title: 'Login Successful',
    text: 'Redirecting...',
    icon: 'success',
    timer: 1200,
    showConfirmButton: false
  }).then(() => {
    if (data.user.role === 'superadmin') location.href = 'superadmin_home.php';
    else location.href = 'admin_home.php';
  });
});
</script>

</body>
</html>

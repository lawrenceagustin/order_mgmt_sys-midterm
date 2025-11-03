<?php
require_once 'db_config.php';
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>

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
    <div class="max-w-5xl mx-auto px-6 flex justify-between items-center">
      <h1 class="text-3xl font-bold tracking-wide">
        Admin Dashboard (Cashier)
      </h1>
      <nav class="flex gap-2">
        <a href="products.php" 
           class="px-4 py-2 bg-white text-primary font-semibold rounded-lg shadow hover:bg-accent hover:text-white transition">
           Products
        </a>
        <a href="reports.php" 
           class="px-4 py-2 bg-white text-primary font-semibold rounded-lg shadow hover:bg-accent hover:text-white transition">
           Reports
        </a>

        <a href="logout.php" 
           class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow hover:bg-red-700 transition">
           Logout
        </a>
      </nav>
    </div>
  </header>

  <main class="max-w-4xl mx-auto mt-10 px-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
      <h2 class="text-2xl font-semibold text-primary mb-4">
        Welcome, <?php echo htmlspecialchars($_SESSION['user']['firstname']); ?>!
      </h2>
      <p class="text-gray-700 text-lg leading-relaxed">
        Use the navigation buttons above to manage products, receive customer orders through the POS system, and view transaction reports.
      </p>
      <div class="mt-6 p-4 bg-accent/20 border-l-4 border-accent rounded-lg">
        <p class="text-primary font-medium">
          You are logged in with <strong>Admin (Cashier)</strong> privileges.
        </p>
      </div>
    </div>
  </main>
</body>
</html>
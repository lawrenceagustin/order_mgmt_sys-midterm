<?php
require_once 'db_config.php';
header('Content-Type: application/json');


$body = file_get_contents('php://input');
$data = json_decode($body, true) ?? $_POST;
$action = $_GET['action'] ?? $data['action'] ?? null;

function json_error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}
function json_ok($payload = []) {
    echo json_encode($payload);
    exit;
}

function require_login() {
    if (empty($_SESSION['user'])) json_error('Not authenticated', 401);
}
function require_admin_or_super() {
    require_login();
    if (!in_array($_SESSION['user']['role'], ['admin', 'superadmin'])) json_error('Admin only', 403);
}
function require_superadmin() {
    require_login();
    if ($_SESSION['user']['role'] !== 'superadmin') json_error('Superadmin only', 403);
}


// admin login
if ($action === 'admin_login') {
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    if ($username === '' || $password === '') json_error('Username and password required.');
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role IN ('admin','superadmin') LIMIT 1");
    $stmt->execute([$username]);
    $u = $stmt->fetch();
    if (!$u) json_error('Invalid credentials.');
    if ($u['status'] !== 'active') json_error('Account suspended.');
    if (!password_verify($password, $u['password'])) json_error('Invalid credentials.');

    $_SESSION['user'] = [
        'id' => (int)$u['id'],
        'username' => $u['username'],
        'role' => $u['role'],
        'firstname' => $u['firstname'],
        'lastname' => $u['lastname']
    ];
    json_ok(['message' => 'Logged in', 'user' => $_SESSION['user']]);
}

// logout
if ($action === 'logout') {
    session_unset();
    session_destroy();
    json_ok(['message' => 'Logged out']);
}

// add product
if ($action === 'add_product') {
    require_admin_or_super();
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    if ($name === '' || $price <= 0) json_error('Name and price required.');
    $imagePath = null;
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image']['tmp_name'];
        $orig = basename($_FILES['image']['name']);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!in_array(strtolower($ext), $allowed)) json_error('Invalid image type.');
        $new = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $orig);
        if (!move_uploaded_file($tmp, __DIR__ . '/uploads/' . $new)) {
            json_error('Failed to save image.');
        }
        $imagePath = 'uploads/' . $new;
    }
    $stmt = $pdo->prepare("INSERT INTO products (name, price, image, added_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $imagePath, $_SESSION['user']['id']]);
    json_ok(['message' => 'Product added.']);
}

// get products
if ($action === 'get_products') {
    $stmt = $pdo->query("SELECT p.*, u.username as added_by_username FROM products p LEFT JOIN users u ON p.added_by = u.id ORDER BY p.date_added DESC");
    $products = $stmt->fetchAll();
    json_ok(['products' => $products]);
}

// place order
if ($action === 'place_order') {
    $items = $data['items'] ?? [];
    $total = (float)($data['total'] ?? 0);
    $customer_name = trim($data['customer_name'] ?? '') ?: 'Guest';
    if (empty($items) || $total <= 0) json_error('Invalid order.');
    $order_number = 'ORD' . time();
    try {
        $pdo->beginTransaction();
        $ins = $pdo->prepare("INSERT INTO orders (order_number, total, customer_name) VALUES (?, ?, ?)");
        $ins->execute([$order_number, $total, $customer_name]);
        $order_id = $pdo->lastInsertId();
        $insItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
        foreach ($items as $it) {
            $pid = (int)$it['product_id'];
            $qty = (int)$it['quantity'];
            $subtotal = (float)$it['subtotal'];
            $insItem->execute([$order_id, $pid, $qty, $subtotal]);
        }
        $pdo->commit();
        json_ok(['message' => 'Order placed.', 'order_number' => $order_number]);
    } catch (Exception $e) {
        $pdo->rollBack();
        json_error('Failed to place order: ' . $e->getMessage(), 500);
    }
}

//create admin
if ($action === 'create_admin') {
    require_superadmin();
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $firstname = trim($data['firstname'] ?? '');
    $lastname = trim($data['lastname'] ?? '');
    if ($username === '' || $password === '' || $firstname === '' || $lastname === '') json_error('All fields are required.');
    if (strlen($password) < 6) json_error('Password must be at least 6 characters.');
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    if ($stmt->fetch()) json_error('Username already taken.');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $pdo->prepare("INSERT INTO users (username, password, firstname, lastname, role) VALUES (?, ?, ?, ?, 'admin')");
    $ins->execute([$username, $hash, $firstname, $lastname]);
    json_ok(['message' => 'Admin account created.']);
}

// suspend/reactivate user
if ($action === 'suspend_user') {
    require_superadmin();
    $user_id = (int)($data['user_id'] ?? 0);
    if ($user_id <= 0) json_error('Invalid user id.');
    $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
    $stmt->execute([$user_id]);
    json_ok(['message' => 'User suspended.']);
}
if ($action === 'reactivate_user') {
    require_superadmin();
    $user_id = (int)($data['user_id'] ?? 0);
    if ($user_id <= 0) json_error('Invalid user id.');
    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $stmt->execute([$user_id]);
    json_ok(['message' => 'User reactivated.']);
}

// get users
if ($action === 'get_users') {
    require_superadmin();
    $stmt = $pdo->query("SELECT id, username, firstname, lastname, role, status, date_added FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll();
    json_ok(['users' => $users]);
}

// get orders
if ($action === 'get_orders') {
    require_admin_or_super();
    $start = $data['date_start'] ?? null;
    $end = $data['date_end'] ?? null;
    $sql = "SELECT o.*, u.username as cashier FROM orders o LEFT JOIN users u ON o.cashier_id = u.id";
    $params = [];
    if ($start && $end) {
        $sql .= " WHERE DATE(o.date_added) BETWEEN ? AND ?";
        $params = [$start, $end];
    } elseif ($start) {
        $sql .= " WHERE DATE(o.date_added) >= ?";
        $params = [$start];
    } elseif ($end) {
        $sql .= " WHERE DATE(o.date_added) <= ?";
        $params = [$end];
    }
    $sql .= " ORDER BY o.date_added DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    foreach ($orders as &$ord) {
        $s = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $s->execute([$ord['id']]);
        $ord['items'] = $s->fetchAll();
    }
    json_ok(['orders' => $orders]);
}


// delete product
if ($action === 'delete_product') {
    require_admin_or_super();

    $product_id = (int)($data['product_id'] ?? 0);
    if ($product_id <= 0) json_error('Invalid product ID.');
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    json_ok(['message' => 'Product deleted.']);
}

json_error('No action or invalid action provided.', 400);


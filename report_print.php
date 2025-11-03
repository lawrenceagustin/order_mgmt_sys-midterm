<?php
require_once 'db_config.php';
if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','superadmin'])) {
    echo "Unauthorized";
    exit;
}

$start = $_GET['start'] ?? null;
$end   = $_GET['end'] ?? null;
$query = "
    SELECT 
        orders.id,
        orders.order_number,
        orders.total,
        orders.customer_name,
        orders.cashier_id,
        orders.date_added,
        users.username AS cashier_username
    FROM orders
    LEFT JOIN users 
        ON orders.cashier_id = users.id
";

$parameters = [];

if ($start && $end) {
    $query .= " WHERE DATE(orders.date_added) BETWEEN ? AND ?";
    $parameters = [$start, $end];
} elseif ($start) {
    $query .= " WHERE DATE(orders.date_added) >= ?";
    $parameters = [$start];
} elseif ($end) {
    $query .= " WHERE DATE(orders.date_added) <= ?";
    $parameters = [$end];
}

$query .= " ORDER BY orders.date_added DESC";

$statement = $pdo->prepare($query);
$statement->execute($parameters);
$orders = $statement->fetchAll();
foreach ($orders as &$order) {

    $orderItemsQuery = $pdo->prepare("
        SELECT 
            order_items.id,
            order_items.order_id,
            order_items.product_id,
            order_items.quantity,
            order_items.subtotal,
            products.name AS product_name
        FROM order_items
        LEFT JOIN products 
            ON order_items.product_id = products.id
        WHERE order_items.order_id = ?
    ");

    $orderItemsQuery->execute([$order['id']]);
    $order['items'] = $orderItemsQuery->fetchAll();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Printable Orders</title>
<style>
table { width:100%; border-collapse:collapse }
th,td { border:1px solid #333; padding:6px; text-align:left }
tfoot td { font-weight:bold }
</style>
</head>
<body>

<h2>Orders Report</h2>
<p>
  From: <?= htmlspecialchars($start) ?> 
  &nbsp; To: <?= htmlspecialchars($end) ?>
</p>

<table>
  <thead>
    <tr>
      <th>Order Number</th>
      <th>Date</th>
      <th>Customer Name</th>
      <th>Items</th>
      <th>Total Amount</th>
    </tr>
  </thead>

  <tbody>
    <?php $grandTotal = 0; foreach ($orders as $order): ?>
      <tr>
        <td><?= htmlspecialchars($order['order_number']) ?></td>
        <td><?= htmlspecialchars($order['date_added']) ?></td>
        <td><?= htmlspecialchars($order['customer_name']) ?></td>

        <td>
          <?php foreach ($order['items'] as $item): ?>
            <?= htmlspecialchars($item['product_name']) ?> x<?= (int)$item['quantity'] ?><br>
          <?php endforeach; ?>
        </td>

        <td>₱<?= number_format($order['total'], 2) ?></td>
      </tr>

      <?php $grandTotal += $order['total']; ?>
    <?php endforeach; ?>
  </tbody>

  <tfoot>
    <tr>
      <td colspan="4" style="text-align:right;">Grand Total:</td>
      <td>₱<?= number_format($grandTotal, 2) ?></td>
    </tr>
  </tfoot>
</table>

<script>window.print();</script>
</body>
</html>

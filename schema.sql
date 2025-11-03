CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(80) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `cashier_id` int(11) DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
)

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
)

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
)

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `role` enum('admin','superadmin') NOT NULL DEFAULT 'admin',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
)
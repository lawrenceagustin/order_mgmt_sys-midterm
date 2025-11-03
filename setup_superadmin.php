<?php
require_once 'db_config.php';
$username = 'superadmin';
$password = 'superpassword';
$firstname = 'Super';
$lastname = 'Admin';

$stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'superadmin' LIMIT 1");
$stmt->execute();
if ($stmt->fetch()) {
    echo "Superadmin already exists. Delete this file.";
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $pdo->prepare("INSERT INTO users (username,password,firstname,lastname,role) VALUES (?, ?, ?, ?, 'superadmin')");
$ins->execute([$username, $hash, $firstname, $lastname]);

echo "Superadmin created. Username: {$username} Password: {$password}. Delete or secure this file.";

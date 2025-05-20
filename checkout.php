<?php
session_start();
require 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];

// Check if cart session exists and is not empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty.";
    header('Location: cart.php');
    exit;
}

// Copy cart items to a temporary variable before clearing session
$cartItems = $_SESSION['cart'];  // <-- store cart items and quantities here

// Prepare to fetch product details from cart session
$productIds = array_keys($cartItems);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
$stmt->execute($productIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$totalAmount = 0;
foreach ($products as $product) {
    $pid = $product['id'];
    $qty = $cartItems[$pid];
    $totalAmount += $product['price'] * $qty;
}

try {
    $pdo->beginTransaction();

    // Insert into orders
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->execute([$userId, $totalAmount]);
    $orderId = $pdo->lastInsertId();

    // Insert order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($products as $product) {
        $pid = $product['id'];
        $qty = $cartItems[$pid];
        $stmt->execute([$orderId, $pid, $qty, $product['price']]);
    }

    // Clear the cart session AFTER saving quantities to $cartItems
    unset($_SESSION['cart']);

    $pdo->commit();

    // Show confirmation page below instead of redirecting
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Checkout failed: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order Confirmation - PetConnect</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
<header>
    <div class="logo">🐾 PetConnect</div>
    <nav>
        <a href="Home.php">🏠 Home</a>
        <a href="shop.php">🛒 Shop</a>
        <a href="cart.php">🛍️ Cart</a>
        <a href="orders.php">📦 My Orders</a>
        <div class="dropdown">
            <button class="dropbtn">⚙️ Account ▼</button>
            <div class="dropdown-content">
                <a href="profile.php">Profile</a>
                <a href="logout_confirmation.php">Logout</a>
            </div>
        </div>
    </nav>
</header>

<div class="confirmation-container">
    <h1>Thank you for your order!</h1>
    <p>Your order #<strong><?= htmlspecialchars($orderId) ?></strong> has been successfully placed.</p>
    <p><strong>Order Total:</strong> $<?= number_format($totalAmount, 2) ?></p>

    <h2>Order Details:</h2>
    <ul>
        <?php foreach ($products as $product):
            $qty = $cartItems[$product['id']] ?? 0;  // Use $cartItems instead of $_SESSION['cart']
        ?>
        <li><?= htmlspecialchars($product['name']) ?> — Quantity: <?= $qty ?> — Price: $<?= number_format($product['price'], 2) ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="shop.php" class="button">Continue Shopping</a>
    <a href="orders.php" class="button">View My Orders</a>
</div>
</body>
</html>

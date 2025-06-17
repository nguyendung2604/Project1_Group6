<?php

session_start();
require_once 'db_connect.php';

// Cart count for icon badge
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Check for direct buy
if (isset($_GET['product_id']) && isset($_GET['quantity'])) {
    $pid = (int)$_GET['product_id'];
    $qty = (int)$_GET['quantity'];
    if ($pid > 0 && $qty > 0) {
        $cart = [$pid => $qty];
    }
} else {
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        header("Location: cart.php");
        exit;
    }
}

// Fetch product details
$ids = implode(',', array_map('intval', array_keys($cart)));
$sql = "SELECT product_id, name, price FROM products WHERE product_id IN ($ids)";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($products as &$product) {
    $qty = $cart[$product['product_id']];
    $product['qty'] = $qty;
    $product['subtotal'] = $qty * $product['price'];
    $total += $product['subtotal'];
}

// Handle order submission
$order_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['address'], $_POST['payment_method'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $shipping_address = $_POST['address'];
    $status = 'pending'; // or 'delivered' if you want
    $total_price = $total;

    try {
        // 1. Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, shipping_address, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $total_price, $shipping_address, $status]);
        $order_id = $pdo->lastInsertId();

        // 2. Insert each item into order_items table
        foreach ($products as $product) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $product['product_id'],
                $product['qty'],
                $product['price']
            ]);
        }

        // 3. Clear cart
        $_SESSION['cart'] = [];
        $order_message = "Thank you for your purchase! Your order has been placed.";

    } catch (PDOException $e) {
        $order_message = "Error placing order: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wireless World</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="style.css">
    
    <style>
        .card-info { display: none; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand brand-font text-primary me-4" href="index.php">Wireless World</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="d-flex align-items-center order-lg-last">
                <!-- Search Bar -->
                <div class="position-relative me-3">
                    <input type="text" class="form-control pe-5 search-input" placeholder="Search phones...">
                    <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                        <i class="bi bi-search text-muted"></i>
                    </div>
                </div>

                <!-- Icons -->
                <a href="cart.php" class="btn btn-link text-dark p-2 me-2 nav-icon position-relative" aria-label="Shopping Cart">
                    <i class="bi bi-bag"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.75rem;">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>
                
                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark p-2 me-2 nav-icon" type="button" id="userDropdown" 
                            data-bs-toggle="dropdown" aria-expanded="false" aria-label="User Profile">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="login.php">Login</a></li>
                        <li><a class="dropdown-item" href="register.php">Register</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#brands">Brands</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link fw-medium" href="#catrgory">Catrgory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#deals">Deals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#about">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<div class="container" style="padding-top:90px; max-width:600px;">
    <h2 class="fs-2 fw-bold text-dark mb-2">Checkout</h2>
    <?php if ($order_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($order_message) ?></div>
    <?php else: ?>
    <h5>Order Summary</h5>
    <ul class="list-group mb-4">
        <?php foreach ($products as $item): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($item['name']) ?> x <?= $item['qty'] ?>
                <span><?= number_format($item['subtotal'], 0, '.', ',') ?>₫</span>
            </li>
        <?php endforeach; ?>
        <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
            Total
            <span><?= number_format($total, 0, '.', ',') ?>₫</span>
        </li>
    </ul>
    <form method="post" class="mb-5" id="checkout-form" autocomplete="off">
        <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Shipping address</label>
            <textarea name="address" id="address" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="payment_method" id="pay_card" value="card" required>
                    <label class="form-check-label" for="pay_card">Credit/Debit Card</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod" required>
                    <label class="form-check-label" for="pay_cod">Pay When Delivered</label>
                </div>
            </div>
        </div>
        <div class="mb-3 card-info" id="card-info-section">
            <label class="form-label">Card Information</label>
            <input type="text" class="form-control mb-2" name="card_number" placeholder="Card Number">
            <div class="row">
                <div class="col">
                    <input type="text" class="form-control mb-2" name="card_expiry" placeholder="MM/YY">
                </div>
                <div class="col">
                    <input type="text" class="form-control mb-2" name="card_cvc" placeholder="CVC">
                </div>
            </div>
            <input type="text" class="form-control mb-2" name="card_name" placeholder="Name on Card">
        </div>
        <button type="submit" class="btn btn-primary w-100">Place Order</button>
    </form>
    <?php endif; ?>
</div>
<!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-lg-3 col-md-6">
                    <a href="#" class="brand-font text-white text-decoration-none h4 d-block mb-3">Wireless World</a>
                    <p class="text-muted mb-4">Your ultimate destination for smartphone comparison and discovery. Find the perfect phone that matches your needs and budget.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-icon">
                            <i class="bi-facebook fs-5"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi-twitter-x fs-5"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi-instagram fs-5"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="bi-youtube fs-5"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h3 class="h5 fw-semibold mb-3">Quick Links</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#brands" class="text-muted text-decoration-none">Browse Brands</a></li>
                        <li class="mb-2"><a href="#compare" class="text-muted text-decoration-none">Compare Phones</a></li>
                        <li class="mb-2"><a href="#deals" class="text-muted text-decoration-none">Deals & Offers</a></li>
                        <li class="mb-2"><a href="#about" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="#contact" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h3 class="h5 fw-semibold mb-3">Top Brands</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Samsung</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Apple</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Google</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Xiaomi</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Sony</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">View All Brands</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h3 class="h5 fw-semibold mb-3">Support</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Cookie Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Sitemap</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Accessibility</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-top border-secondary pt-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="text-muted small mb-3 mb-md-0">© 2025 MobileMaster. All rights reserved.</p>
                    </div>
                    
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex align-items-center justify-content-md-end gap-3">
                            <span class="text-muted small">Payment Methods:</span>
                            <div class="d-flex gap-2">
                                <i class="bi-credit-card-2-front fs-5"></i>
                                <i class="bi-paypal fs-5"></i>
                                <i class="bi-apple fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cardRadio = document.getElementById('pay_card');
    const codRadio = document.getElementById('pay_cod');
    const cardInfo = document.getElementById('card-info-section');
    function toggleCardInfo() {
        if (cardRadio.checked) {
            cardInfo.style.display = 'block';
            // Make card fields required
            cardInfo.querySelectorAll('input').forEach(i => i.required = true);
        } else {
            cardInfo.style.display = 'none';
            cardInfo.querySelectorAll('input').forEach(i => i.required = false);
        }
    }
    cardRadio.addEventListener('change', toggleCardInfo);
    codRadio.addEventListener('change', toggleCardInfo);
});
</script>
</body>
</html>
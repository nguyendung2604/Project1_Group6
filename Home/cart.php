<?php
session_start(); // This must be the first line!
require_once 'db_connect.php';

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;

// Helper: Get or create cart_id for user
function getOrCreateCartId($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_id = $stmt->fetchColumn();
    if (!$cart_id) {
        $pdo->prepare("INSERT INTO carts (user_id, total_price) VALUES (?, 0)")->execute([$user_id]);
        $cart_id = $pdo->lastInsertId();
    }
    return $cart_id;
}

// Helper: Get product price from database
function getProductPrice($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetchColumn();
}

// Cart count for icon badge
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Xử lý cập nhật/xóa sản phẩm trong giỏ hàng
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $product_id => $qty) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }
    // If logged in, sync to database
    if ($user_id) {
        $cart_id = getOrCreateCartId($pdo, $user_id);
        // Remove all old items for this cart
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cart_id]);
        // Insert current cart items with price
        foreach ($_SESSION['cart'] as $product_id => $qty) {
            $price = getProductPrice($pdo, $product_id);
            if ($price !== false) { // Only insert if product exists and has price
                $stmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$cart_id, $product_id, $qty, $price]);
            }
        }
        
        // Update total price in carts table
        $stmt = $pdo->prepare("
            UPDATE carts 
            SET total_price = (
                SELECT COALESCE(SUM(ci.quantity * ci.price), 0) 
                FROM cart_items ci 
                WHERE ci.cart_id = ?
            ) 
            WHERE cart_id = ?
        ");
        $stmt->execute([$cart_id, $cart_id]);
    }
}

if (isset($_POST['remove'])) {
    $remove_id = $_POST['remove'];
    unset($_SESSION['cart'][$remove_id]);
    // If logged in, sync to database
    if ($user_id) {
        $cart_id = getOrCreateCartId($pdo, $user_id);
        $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?")->execute([$cart_id, $remove_id]);
        
        // Update total price in carts table
        $stmt = $pdo->prepare("
            UPDATE carts 
            SET total_price = (
                SELECT COALESCE(SUM(ci.quantity * ci.price), 0) 
                FROM cart_items ci 
                WHERE ci.cart_id = ?
            ) 
            WHERE cart_id = ?
        ");
        $stmt->execute([$cart_id, $cart_id]);
    }
}

// Lấy thông tin sản phẩm trong giỏ hàng
$cart = $_SESSION['cart'] ?? [];
$products = [];
$total = 0;
if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $sql = "SELECT p.product_id, p.name, p.price, pi.image_url FROM products p
            LEFT JOIN product_images pi ON p.product_id = pi.product_id
            WHERE p.product_id IN ($ids) GROUP BY p.product_id";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as &$product) {
        $qty = $cart[$product['product_id']];
        $product['qty'] = $qty;
        $product['subtotal'] = $qty * $product['price'];
        $total += $product['subtotal'];
    }
}

// Recalculate cart count
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    
   
</head>
<body class="bg-light">
     <!-- Header -->
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
<section class="bg-white py-5">
    <div class="container py-5 ">
    
    <h2 class="mb-4">Your Cart</h2>
    <?php if (empty($products)): ?>
        <div class="alert alert-info">Your cart is empty.</div>
    <?php else: ?>
    <form method="post">
    <table class="table align-middle table-bordered bg-white">
        <thead>
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $item): ?>
            <tr>
                <td style="width:80px"><img src="<?= htmlspecialchars($item['image_url'] ?? 'HeroSection.jpg') ?>" width="60"></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td>$<?= number_format($item['price']) ?></td>
                <td style="width:100px">
                    <input type="number" name="quantities[<?= $item['product_id'] ?>]" value="<?= $item['qty'] ?>" min="1" class="form-control form-control-sm">
                </td>
                <td>$<?= number_format($item['subtotal']) ?></td>
                <td>
                    <button name="remove" value="<?= $item['product_id'] ?>" class="btn btn-danger btn-sm">Remove</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total:</th>
                <th colspan="2">$<?= number_format($total) ?></th>
            </tr>
        </tfoot>
    </table>
    <div class="d-flex justify-content-between">
        <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
        <a href="checkout.php" class="btn btn-success">Checkout</a>
    </div>
    </form>
    <?php endif; ?>
    <div class="mt-4">
        <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
    </div>
</div>
</section>

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
</body>
</html>
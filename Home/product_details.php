<?php
session_start();
// Kết nối database
require_once 'db_connect.php'; // Make sure you have this at the top

// Cart count for icon badge
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Function to get or create cart ID for the user
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

// Handle Add to Cart
$cart_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $pid = (int)$_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    if ($pid > 0 && $qty > 0) {
        $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + $qty;

        // Sync to DB if logged in
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $cart_id = getOrCreateCartId($pdo, $user_id);

            // Check if item exists
            $stmt = $pdo->prepare("SELECT cart_item_id FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cart_id, $pid]);
            if ($stmt->fetchColumn()) {
                // Update quantity
                $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?")
                    ->execute([$_SESSION['cart'][$pid], $cart_id, $pid]);
            } else {
                // Get product price
                $priceStmt = $pdo->prepare("SELECT price FROM products WHERE product_id = ?");
                $priceStmt->execute([$pid]);
                $price = $priceStmt->fetchColumn();
                $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")
                    ->execute([$cart_id, $pid, $qty, $price]);
            }
        }
        $cart_message = "Added to cart!";
    }
}

try {
    // Lấy ID sản phẩm từ URL
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
    if ($product_id <= 0) {
        throw new Exception("Invalid product ID.");
    }

    // Truy vấn chi tiết sản phẩm bao gồm avatar_product
    $stmt = $pdo->prepare("
        SELECT p.product_id, p.name, p.description, p.price, p.quantity, p.avatar_product,
               b.name AS brand_name, p.screen_size, p.ram, p.storage, p.camera, p.battery, p.os, p.cpu
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.product_id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Product not found.");
    }

    // Truy vấn riêng tất cả ảnh phụ của sản phẩm
    $stmt_images = $pdo->prepare("
        SELECT image_url 
        FROM product_images 
        WHERE product_id = ? 
        ORDER BY product_image_id
    ");
    $stmt_images->execute([$product_id]);
    $product_images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);

    // Tạo mảng tất cả ảnh (avatar + ảnh phụ)
    $all_images = [];
    
    // Thêm avatar_product làm ảnh đầu tiên nếu có
    if (!empty($product['avatar_product'])) {
        $all_images[] = ['image_url' => $product['avatar_product'], 'is_avatar' => true];
    }
    // Thêm các ảnh phụ, loại bỏ trùng với avatar
    foreach ($product_images as $img) {
        if ($img['image_url'] !== $product['avatar_product']) {
            $all_images[] = ['image_url' => $img['image_url'], 'is_avatar' => false];
        }
    }

    // Tính giá gốc (giả lập giá sale)
    $original_price = $product['price'] * 1.08;
    $savings = $original_price - $product['price'];

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details | MobileMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="./assets/css/style.css">
  
    <style>
    body { font-family: 'Inter', sans-serif; }
    .custom-radio {
    position: relative;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    background-color: white;
    transition: all 0.2s ease;
}
.custom-radio.checked {
    border-color: #0d6efd;
}
.custom-radio.checked::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #0d6efd;
}
.gallery-thumbnail.active {
    border-color: #0d6efd !important;
}
.gallery-thumbnail {
    border: 2px solid #dee2e6;
    transition: all 0.3s ease;
}
.gallery-thumbnail:hover {
    border-color: #0d6efd;
    opacity: 0.8;
}
.color-option.active {
    border-color: #0d6efd;
    transform: scale(1.1);
}
.storage-option.active {
    border-color: #0d6efd;
    background-color: #eff6ff;
}
.font-pacifico {
    font-family: 'Pacifico', cursive;
}
.cursor-pointer {
    cursor: pointer;
}
.main-image-container {
    background: #f8f9fa;
    border-radius: 10px;
}

    </style>
   
</head>
<body class="bg-light">
    <!-- Header giống index.php -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand brand-font text-primary me-4" href="index.php">Wireless World</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="d-flex align-items-center order-lg-last">
                <div class="position-relative me-3">
                    <input type="text" class="form-control pe-5 search-input" placeholder="Search phones...">
                    <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                        <i class="bi bi-search text-muted"></i>
                    </div>
                </div>
                <a href="cart.php" class="btn btn-link text-dark p-2 me-2 nav-icon position-relative" aria-label="Shopping Cart">
                    <i class="bi bi-bag"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.75rem;">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>
                <div class="dropdown">
                    <button class="btn btn-link text-dark p-2 me-2 nav-icon" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User Profile">
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
                    <li class="nav-item"><a class="nav-link fw-medium" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#brands">Brands</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#catrgory">Catrgory</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#deals">Deals</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#about">About Us</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="pt-5">
        <section class="bg-white py-5">
            <div class="container">
                <div class="row g-5">
                    <div class="col-lg-6">
                        <!-- Main Image -->
                        <div class="main-image-container rounded overflow-hidden mb-3">
                            <div class="d-flex align-items-center justify-content-center" style="height: 400px;">
                                <?php if (!empty($all_images)): ?>
                                    <img src="<?php echo htmlspecialchars($all_images[0]['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="h-100 object-fit-contain" 
                                         id="main-product-image">
                                <?php else: ?>
                                    <img src="default-image.jpg" 
                                         alt="No image available" 
                                         class="h-100 object-fit-contain" 
                                         id="main-product-image">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <?php if (!empty($all_images) && count($all_images) > 1): ?>
                        <div class="d-flex overflow-x-auto gap-3 pb-2">
                            <?php foreach ($all_images as $index => $image): ?>
                                <div class="gallery-thumbnail cursor-pointer flex-shrink-0 rounded <?php echo $index === 0 ? 'active' : ''; ?>" 
                                     style="width: 80px; height: 80px;" 
                                     data-image="<?php echo htmlspecialchars($image['image_url']); ?>">
                                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?> - Image <?php echo $index + 1; ?>" 
                                         class="w-100 h-100 object-fit-cover rounded">
                                    <?php if ($image['is_avatar']): ?>
                                        <div class="position-absolute top-0 start-0">
                                            <span class="badge bg-primary badge-sm">Main</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php elseif (!empty($all_images) && count($all_images) === 1): ?>
                        <!-- Hiển thị thumbnail duy nhất nếu chỉ có 1 ảnh -->
                        <div class="d-flex gap-3 pb-2">
                            <div class="gallery-thumbnail active cursor-pointer flex-shrink-0 rounded" 
                                 style="width: 80px; height: 80px;" 
                                 data-image="<?php echo htmlspecialchars($all_images[0]['image_url']); ?>">
                                <img src="<?php echo htmlspecialchars($all_images[0]['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-100 h-100 object-fit-cover rounded">
                                <?php if ($all_images[0]['is_avatar']): ?>
                                    <div class="position-absolute top-0 start-0">
                                        <span class="badge bg-primary badge-sm">Main</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center mb-2">
                            <?php if ($product['quantity'] > 0): ?>
                                <span class="badge bg-success bg-opacity-10 text-success">In Stock</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger">Out of Stock</span>
                            <?php endif; ?>
                            <?php if ($savings > 0): ?>
                                <span class="badge bg-danger ms-2">SALE</span>
                            <?php endif; ?>
                        </div>
                        
                        <h1 class="fs-2 fw-bold text-dark mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="d-flex align-items-center mb-4">
                            <span class="text-secondary me-2">Brand:</span>
                            <span class="fw-medium"><?php echo htmlspecialchars($product['brand_name']); ?></span>
                            <?php if ($product['quantity'] > 0): ?>
                                <span class="mx-3 text-muted">|</span>
                                <span class="text-secondary me-2">Stock:</span>
                                <span class="fw-medium text-success"><?php echo $product['quantity']; ?> available</span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-muted mb-4"><?php echo htmlspecialchars($product['description']); ?></p>

                        <!-- Technical Specifications -->
                        <div class="mb-4">
                            <h3 class="fs-5 fw-semibold mb-3">Technical Specifications</h3>
                            <div class="row g-3">
                                <?php if (!empty($product['screen_size'])): ?>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="small text-muted">Screen Size</div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($product['screen_size']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['ram'])): ?>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="small text-muted">RAM</div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($product['ram']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['storage'])): ?>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="small text-muted">Storage</div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($product['storage']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['camera'])): ?>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="small text-muted">Camera</div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($product['camera']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['battery'])): ?>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="small text-muted">Battery</div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($product['battery']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['os'])): ?>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="small text-muted">Operating System</div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($product['os']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['cpu'])): ?>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="small text-muted">Processor</div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($product['cpu']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="d-flex align-items-center mb-4">
                            <div class="fs-3 fw-bold text-dark" id="product-price">$<?php echo number_format($product['price']); ?></div>
                            <?php if ($savings > 0): ?>
                                <div class="ms-3 fs-5 text-muted text-decoration-line-through" id="original-price">$<?php echo number_format($original_price); ?></div>
                                <div class="ms-3 badge bg-danger">SAVE $<?php echo number_format($savings); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Add to Cart Section -->
                        <?php if ($product['quantity'] > 0): ?>
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <!-- Add to Cart Form -->
                                <form action="" method="POST" class="d-flex">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <div class="d-flex border rounded overflow-hidden me-2">
                                        <button type="button" class="btn btn-outline-secondary border-end" id="decrease-quantity">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="form-control text-center border-0" style="width: 80px;" id="quantity-input">
                                        <button type="button" class="btn btn-outline-secondary border-start" id="increase-quantity">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <button type="submit" class="btn btn-primary py-3">
                                        <i class="bi bi-cart "></i> Add to Cart
                                    </button>
                                </form>
                                <!-- Buy Now Form -->
                                <form action="checkout.php" method="get" class="d-flex flex-grow-1">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="hidden" name="quantity" id="buy-now-quantity" value="1">
                                    <button type="submit" class="btn btn-success w-100 py-3">
                                        <i class="bi bi-lightning-charge "></i> Buy Now
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mb-5">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                This product is currently out of stock and unavailable for purchase.
                            </div>
                        <?php endif; ?>

                        <!-- Shipping & Warranty Info -->
                        <div class="border-top pt-4">
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-truck text-success me-2"></i>
                                    <span class="small">Free shipping for orders over $35</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-check text-success me-2"></i>
                                    <span class="small">Delivery: 1-3 business days</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-arrow-repeat text-success me-2"></i>
                                    <span class="small">30-day money-back guarantee</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-shield-check text-success me-2"></i>
                                    <span class="small">1-year limited warranty</span>
                                </div>
                            </div>
                        </div>

                        <!-- Back to Products Link -->
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i> Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Footer giống index.php -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-lg-3 col-md-6">
                    <a href="#" class="brand-font text-white text-decoration-none h4 d-block mb-3">Wireless World</a>
                    <p class="text-muted mb-4">Your ultimate destination for smartphone comparison and discovery. Find the perfect phone that matches your needs and budget.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-icon"><i class="bi-facebook fs-5"></i></a>
                        <a href="#" class="social-icon"><i class="bi-twitter-x fs-5"></i></a>
                        <a href="#" class="social-icon"><i class="bi-instagram fs-5"></i></a>
                        <a href="#" class="social-icon"><i class="bi-youtube fs-5"></i></a>
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
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">OnePlus</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">View All Brands</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h3 class="h5 fw-semibold mb-3">Support</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary pt-4">
                <div class="row align-items-center">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 text-md-end"></div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Thumbnail gallery functionality
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.gallery-thumbnail');
            const mainImage = document.getElementById('main-product-image');
            
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    // Remove active class from all thumbnails
                    thumbnails.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked thumbnail
                    this.classList.add('active');
                    
                    // Change main image
                    const newImageSrc = this.getAttribute('data-image');
                    mainImage.src = newImageSrc;
                });
            });

            // Quantity controls
            const decreaseBtn = document.getElementById('decrease-quantity');
            const increaseBtn = document.getElementById('increase-quantity');
            const quantityInput = document.getElementById('quantity-input');
            
            if (decreaseBtn && increaseBtn && quantityInput) {
                decreaseBtn.addEventListener('click', function() {
                    let currentValue = parseInt(quantityInput.value);
                    if (currentValue > 1) {
                        quantityInput.value = currentValue - 1;
                    }
                });
                
                increaseBtn.addEventListener('click', function() {
                    let currentValue = parseInt(quantityInput.value);
                    let maxValue = parseInt(quantityInput.getAttribute('max'));
                    if (currentValue < maxValue) {
                        quantityInput.value = currentValue + 1;
                    }
                });
            }

            // Sync quantity for Buy Now
            const buyNowQuantity = document.getElementById('buy-now-quantity');
            if (quantityInput && buyNowQuantity) {
                quantityInput.addEventListener('input', function() {
                    buyNowQuantity.value = quantityInput.value;
                });
                // Also sync on plus/minus buttons if you have them
                document.getElementById('decrease-quantity').addEventListener('click', function() {
                    quantityInput.stepDown();
                    quantityInput.dispatchEvent(new Event('input'));
                });
                document.getElementById('increase-quantity').addEventListener('click', function() {
                    quantityInput.stepUp();
                    quantityInput.dispatchEvent(new Event('input'));
                });
            }
        });
    </script>
</body>
</html>
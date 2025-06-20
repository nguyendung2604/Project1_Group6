<?php
session_start();
require 'db_connect.php';

// Get compare product IDs from session
$compare_ids = isset($_SESSION['compare']) ? $_SESSION['compare'] : [];
$products = [];
if (!empty($compare_ids)) {
    $placeholders = implode(',', array_fill(0, count($compare_ids), '?'));
    $stmt = $pdo->prepare("SELECT p.*, b.name AS brand_name, c.name AS category_name
        FROM products p
        JOIN brands b ON p.brand_id = b.brand_id
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id IN ($placeholders)");
    $stmt->execute($compare_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Handle remove from compare
if (isset($_POST['remove_compare'])) {
    $remove_id = (int)$_POST['remove_compare'];
    if (($key = array_search($remove_id, $_SESSION['compare'])) !== false) {
        unset($_SESSION['compare'][$key]);
        $_SESSION['compare'] = array_values($_SESSION['compare']);
    }
    header('Location: compare.php');
    exit;
}

// Lấy danh sách sản phẩm cần so sánh
$sqlList = "SELECT * FROM products";
$stmtList = $pdo->query($sqlList);
$productsList = $stmtList->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Comparison</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">Product Comparison</h2>
    <?php if (empty($products)): ?>
        <p>You have not selected any products to compare.</p>
        <a href="index.php" class="btn btn-primary">Back to Home</a>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Brand</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Screen Size</th>
                    <th>RAM</th>
                    <th>Storage</th>
                    <th>Camera</th>
                    <th>Battery</th>
                    <th>OS</th>
                    <th>CPU</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><img src="../<?php echo htmlspecialchars($p['avatar_product']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width:80px;"></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td>$<?php echo number_format($p['price']); ?></td>
                    <td><?php echo $p['brand_name']; ?></td>
                    <td><?php echo $p['category_name']; ?></td>
                    <td><?php echo $p['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($p['screen_size']); ?></td>
                    <td><?php echo htmlspecialchars($p['ram']); ?></td>
                    <td><?php echo htmlspecialchars($p['storage']); ?></td>
                    <td><?php echo htmlspecialchars($p['camera']); ?></td>
                    <td><?php echo htmlspecialchars($p['battery']); ?></td>
                    <td><?php echo htmlspecialchars($p['os']); ?></td>
                    <td><?php echo htmlspecialchars($p['cpu']); ?></td>
                    <td>
                        <form method="post" action="compare.php">
                            <input type="hidden" name="remove_compare" value="<?php echo $p['product_id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
    
    <?php endif; ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
        + Add Product
    </button>
    <!-- Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Select products to add to comparison</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Image</th>
                                        <th>Product Name</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productsList as $product): ?>
                                        <tr>
                                            <td style="width: 50px;">
                                                <img src="../<?php echo htmlspecialchars($product['avatar_product']); ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                                     style="height: 80px; object-fit: contain;">
                                            </td>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td class="text-danger fw-bold">$<?= number_format($product['price']) ?></td>
                                            <td>
                                                <?php if (in_array($product['product_id'], $compare_ids)): ?>
                                                    <button class="btn btn-secondary w-100" disabled>Added</button>
                                                <?php else: ?>
                                                    <a href="add-compare.php?id=<?= $product['product_id'] ?>" class="btn btn-success w-100">
                                                        + Add To Compare
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

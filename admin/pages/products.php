<h2 class="mb-4">Product Management</h2>

<div class="table-container">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>ProductName</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.*, c.name as category_name, b.name as brand_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.category_id 
                    LEFT JOIN brands b ON p.brand_id = b.brand_id
                    ORDER BY p.product_id ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($products as $row):
            ?>
            <tr>
                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['price']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo $row['category_name']; ?></td>
                <td><?php echo $row['brand_name']; ?></td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <button type="submit" name="delete_product" class="btn btn-danger-custom btn-sm btn-custom" 
                                onclick="return confirm('Are you sure you want to delete this product?')">Delete
                        </button>
                    </form>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <button type="submit" name="show_edit_product" class="btn btn-warning btn-sm btn-custom">Edit</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Hiển thị form sửa nếu có yêu cầu sửa
$edit_product = null;
if (isset($_POST['show_edit_product'])) {
    $edit_id = $_POST['product_id'];
    $sql = "SELECT * FROM products WHERE product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $edit_id, PDO::PARAM_INT);
    $stmt->execute();
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Lấy danh sách category và brand
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$brands = $conn->query("SELECT * FROM brands")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($edit_product): ?>
<div class="form-container mb-4">
    <h5 class="mb-3">Edit Product</h5>
    <form method="POST" class="row g-3">
        <input type="hidden" name="product_id" value="<?php echo $edit_product['product_id']; ?>">
        <div class="col-md-3">
            <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($edit_product['description']); ?>" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="price" class="form-control" value="<?php echo $edit_product['price']; ?>" min="0" step="0.01" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="quantity" class="form-control" value="<?php echo $edit_product['quantity']; ?>" min="0" required>
        </div>
        <div class="col-md-2">
            <select name="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php if ($edit_product['category_id'] == $cat['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="brand_id" class="form-select" required>
                <option value="">Select Brand</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo $brand['brand_id']; ?>" <?php if ($edit_product['brand_id'] == $brand['brand_id']) echo 'selected'; ?>><?php echo htmlspecialchars($brand['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" name="edit_product" class="btn btn-primary w-100">
                <i class="fas fa-save me-2"></i>Save
            </button>
        </div>
    </form>
</div>
<?php endif; ?>
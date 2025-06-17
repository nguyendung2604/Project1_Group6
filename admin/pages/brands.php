<h2 class="mb-4">Brand Management</h2>

<?php
// Hiển thị form sửa nếu có yêu cầu sửa
$edit_brand = null;
if (isset($_POST['show_edit_brand'])) {
    $edit_id = $_POST['brand_id'];
    $sql = "SELECT * FROM brands WHERE brand_id = :brand_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':brand_id', $edit_id, PDO::PARAM_INT);
    $stmt->execute();
    $edit_brand = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="form-container">
    <?php if ($edit_brand): ?>
        <h5 class="mb-3">Edit Brand</h5>
        <form method="POST" class="row g-3">
            <input type="hidden" name="brand_id" value="<?php echo $edit_brand['brand_id']; ?>">
            <div class="col-md-8">
                <input type="text" name="brand_name" class="form-control" value="<?php echo htmlspecialchars($edit_brand['name']); ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" name="edit_brand" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Save
                </button>
            </div>
        </form>
    <?php else: ?>
        <h5 class="mb-3">Add new brand</h5>
        <form method="POST" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="brand_name" class="form-control" 
                       placeholder="Enter the brand name..." required>
            </div>
            <div class="col-md-4">
                <button type="submit" name="add_brand" class="btn btn-success-custom btn-custom w-100">
                    <i class="fas fa-plus me-2"></i>Add brand
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<div class="table-container">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>BrandName</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM brands ORDER BY brand_id ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($brands as $row):
            ?>
            <tr>
                <td><?php echo $row['brand_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="brand_id" value="<?php echo $row['brand_id']; ?>">
                        <button type="submit" name="delete_brand" class="btn btn-danger-custom btn-sm btn-custom" 
                                onclick="return confirm('Are you sure you want to delete this brand?')">Delete
                        </button>
                    </form>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="brand_id" value="<?php echo $row['brand_id']; ?>">
                        <button type="submit" name="show_edit_brand" class="btn btn-warning btn-sm btn-custom">Edit</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
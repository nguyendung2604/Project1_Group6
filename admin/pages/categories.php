<h2 class="mb-4">Category Management</h2>

<?php
// Hiển thị form sửa nếu có yêu cầu sửa
$edit_category = null;
if (isset($_POST['show_edit_category'])) {
    $edit_id = $_POST['category_id'];
    $sql = "SELECT * FROM categories WHERE category_id = :category_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category_id', $edit_id, PDO::PARAM_INT);
    $stmt->execute();
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="form-container">
    <?php if ($edit_category): ?>
        <h5 class="mb-3">Edit Category</h5>
        <form method="POST" class="row g-3">
            <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
            <div class="col-md-8">
                <input type="text" name="category_name" class="form-control" value="<?php echo htmlspecialchars($edit_category['name']); ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" name="edit_category" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Save
                </button>
            </div>
        </form>
    <?php else: ?>
        <h5 class="mb-3">Add new categry</h5>
        <form method="POST" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="category_name" class="form-control"
                    placeholder="Enter the category name..." required>
            </div>
            <div class="col-md-4">
                <button type="submit" name="add_category" class="btn btn-success-custom btn-custom w-100">
                    <i class="fas fa-plus me-2"></i>Add category
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
                <th>CategoryName</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM categories ORDER BY category_id ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($categories as $row):
            ?>
                <tr>
                    <td><?php echo $row['category_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="category_id" value="<?php echo $row['category_id']; ?>">
                            <button type="submit" name="delete_category" class="btn btn-danger-custom btn-sm btn-custom"
                                onclick="return confirm('Are you sure you want to delete this category?')">Delete
                            </button>
                        </form>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="category_id" value="<?php echo $row['category_id']; ?>">
                            <button type="submit" name="show_edit_category" class="btn btn-warning btn-sm btn-custom">Edit</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
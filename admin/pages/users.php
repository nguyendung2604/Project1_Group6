<h2 class="mb-4">User Management</h2>

<?php
// Hiển thị form sửa nếu có yêu cầu sửa
$edit_user = null;
if (isset($_POST['show_edit_user'])) {
    $edit_id = $_POST['user_id'];
    $sql = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $edit_id, PDO::PARAM_INT);
    $stmt->execute();
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php if ($edit_user): ?>
<div class="form-container mb-4">
    <h5 class="mb-3">Edit User</h5>
    <form method="POST" class="row g-3">
        <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
        <div class="col-md-3">
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required>
        </div>
        <div class="col-md-4">
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-control">
                <option value="user" <?php if($edit_user['role']==='user') echo 'selected'; ?>>User</option>
                <option value="admin" <?php if($edit_user['role']==='admin') echo 'selected'; ?>>Admin</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" name="edit_user" class="btn btn-primary w-100">
                <i class="fas fa-save me-2"></i>Save
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="table-container">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>UserName</th>
                <th>Pass</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM users ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($users as $row):
            ?>
            <tr>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['password']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['role']; ?></td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger-custom btn-sm btn-custom" 
                                onclick="return confirm('Are you sure you want to delete this user?')">Delete
                        </button>
                    </form>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" name="show_edit_user" class="btn btn-warning btn-sm btn-custom">Edit</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<h2 class="mb-4">Order Management</h2>
<div class="table-container">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>UserName</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Truy vấn lấy danh sách đơn hàng kèm theo username từ bảng users
            $sql = "SELECT o.*, u.username as user_name 
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.user_id 
                    ORDER BY o.order_id ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($orders as $row):
            ?>
            <tr>
                <td><?php echo $row['order_id']; ?></td>
                <td><?php echo $row['user_name']; ?></td>
                <td><?php echo $row['total_price']; ?></td>

                <td>
                    
                        <?php
                        $status_text = [
                            'pending' => 'Đang chờ',
                            'delivered' => 'Đã giao'
                        ];
                        echo $status_text[$row['status']] ?? $row['status'];
                        ?>
                    
                </td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <div class="input-group input-group-sm" style="width: 180px;">
                            <select name="status" class="form-select form-select-sm">
                                <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="delivered" <?php echo $row['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <button type="submit" name="update_order_status" class="btn btn-primary-custom btn-sm">
                              <i class="bi bi-floppy-fill"></i>
                            </button>
                        </div>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
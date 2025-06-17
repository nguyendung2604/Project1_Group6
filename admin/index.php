<?php
// kết nối csdl
require_once 'includes/db_connect.php';

//biến để thông bào
$message = "";
$message_type = "";


// các hành động
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Thêm danh mục
        if (isset($_POST['add_category'])) {
            $category_name = trim($_POST['category_name']);
            if (!empty($category_name)) {
                $sql = "INSERT INTO categories (name) VALUES (:name)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $category_name, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $message = "Thêm danh mục thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi khi thêm danh mục!";
                    $message_type = "danger";
                }
                $stmt = null; // Đóng statement
            }
        }




        
        // Thêm thương hiệu
        if (isset($_POST['add_brand'])) {
            $brand_name = trim($_POST['brand_name']);
            if (!empty($brand_name)) {
                $sql = "INSERT INTO brands (name) VALUES (:name)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $brand_name, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $message = "Thêm thương hiệu thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi khi thêm thương hiệu!";
                    $message_type = "danger";
                }
                $stmt = null; // Đóng statement
            }
        }
        
        // Thêm sản phẩm
        if (isset($_POST['add_product'])) {
            $name = trim($_POST['product_name']);
            $description = trim($_POST['description']);
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];
            $brand_id = $_POST['brand_id'];
            $quantity = $_POST['quantity'];
            $image_url = trim($_POST['image_url']);

            if (!empty($name) && $price > 0 && $quantity >= 0 && !empty($category_id) && !empty($brand_id)) {
                $sql = "INSERT INTO products (name, description, price, category_id, brand_id, quantity) VALUES (:name, :description, :price, :category_id, :brand_id, :quantity)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':price', $price, PDO::PARAM_STR);
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                $stmt->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $product_id = $conn->lastInsertId();
                    
                    // Thêm hình ảnh sản phẩm nếu có
                    if (!empty($image_url)) {
                        $sql_img = "INSERT INTO product_images (product_id, image_url) VALUES (:product_id, :image_url)";
                        $stmt_img = $conn->prepare($sql_img);
                        $stmt_img->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                        $stmt_img->bindParam(':image_url', $image_url, PDO::PARAM_STR);
                        $stmt_img->execute();
                        $stmt_img = null; // Đóng statement
                    }
                    
                    $message = "Thêm sản phẩm thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi khi thêm sản phẩm!";
                    $message_type = "danger";
                }
                $stmt = null; // Đóng statement
            } else {
                $message = "Vui lòng điền đầy đủ thông tin hợp lệ!";
                $message_type = "warning";
            }
        }
        
        // Cập nhật trạng thái đơn hàng
        if (isset($_POST['update_order_status'])) {
            $order_id = $_POST['order_id'];
            $status = $_POST['status'];
            $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $message = "Cập nhật trạng thái đơn hàng thành công!";
                $message_type = "success";
            } else {
                $message = "Lỗi khi cập nhật trạng thái!";
                $message_type = "danger";
            }
            $stmt = null; // Đóng statement
        }
        
        // Xóa người dùng
        if (isset($_POST['delete_user'])) {
            $user_id = $_POST['user_id'];
            $sql = "DELETE FROM users WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $message = "Xóa người dùng thành công!";
                $message_type = "success";
            } else {
                $message = "Lỗi khi xóa người dùng!";
                $message_type = "danger";
            }
            $stmt = null; // Đóng statement
        }
        
        // Xóa danh mục
        if (isset($_POST['delete_category'])) {
            $category_id = $_POST['category_id'];
            $sql = "DELETE FROM categories WHERE category_id = :category_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $message = "Xóa danh mục thành công!";
                $message_type = "success";
            } else {
                $message = "Lỗi khi xóa danh mục!";
                $message_type = "danger";
            }
            $stmt = null; // Đóng statement
        }
        
        // Xóa thương hiệu
        if (isset($_POST['delete_brand'])) {
            $brand_id = $_POST['brand_id'];
            $sql = "DELETE FROM brands WHERE brand_id = :brand_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $message = "Xóa thương hiệu thành công!";
                $message_type = "success";
            } else {
                $message = "Lỗi khi xóa thương hiệu!";
                $message_type = "danger";
            }
            $stmt = null; // Đóng statement
        }
        
        // Xóa sản phẩm
        if (isset($_POST['delete_product'])) {
            $product_id = $_POST['product_id'];
            // Xóa hình ảnh trước
            $sql_img = "DELETE FROM product_images WHERE product_id = :product_id";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt_img->execute();
            $stmt_img = null; // Đóng statement
            
            // Xóa sản phẩm
            $sql = "DELETE FROM products WHERE product_id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $message = "Xóa sản phẩm thành công!";
                $message_type = "success";
            } else {
                $message = "Lỗi khi xóa sản phẩm!";
                $message_type = "danger";
            }
            $stmt = null; // Đóng statement
        }
        
        // Cập nhật danh mục
        if (isset($_POST['edit_category'])) {
            $category_id = $_POST['category_id'];
            $category_name = trim($_POST['category_name']);
            if (!empty($category_id) && !empty($category_name)) {
                $sql = "UPDATE categories SET name = :name WHERE category_id = :category_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $category_name, PDO::PARAM_STR);
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $message = "Cập nhật danh mục thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi khi cập nhật danh mục!";
                    $message_type = "danger";
                }
                $stmt = null;
            } else {
                $message = "Vui lòng nhập tên danh mục hợp lệ!";
                $message_type = "warning";
            }
        }

        // Cập nhật thương hiệu
        if (isset($_POST['edit_brand'])) {
            $brand_id = $_POST['brand_id'];
            $brand_name = trim($_POST['brand_name']);
            if (!empty($brand_id) && !empty($brand_name)) {
                $sql = "UPDATE brands SET name = :name WHERE brand_id = :brand_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $brand_name, PDO::PARAM_STR);
                $stmt->bindParam(':brand_id', $brand_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $message = "Cập nhật thương hiệu thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi khi cập nhật thương hiệu!";
                    $message_type = "danger";
                }
                $stmt = null;
            } else {
                $message = "Vui lòng nhập tên thương hiệu hợp lệ!";
                $message_type = "warning";
            }
        }

        // Cập nhật người dùng
        if (isset($_POST['edit_user'])) {
            $user_id = $_POST['user_id'];
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $role = isset($_POST['role']) ? trim($_POST['role']) : null;
            if (!empty($user_id) && !empty($username) && !empty($email)) {
                $sql = "UPDATE users SET username = :username, email = :email";
                if ($role !== null) {
                    $sql .= ", role = :role";
                }
                $sql .= " WHERE user_id = :user_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                if ($role !== null) {
                    $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                }
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $message = "Cập nhật người dùng thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi khi cập nhật người dùng!";
                    $message_type = "danger";
                }
                $stmt = null;
            } else {
                $message = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
                $message_type = "warning";
            }
        }

        // Cập nhật sản phẩm
        if (isset($_POST['edit_product'])) {
            $product_id = $_POST['product_id'];
            $name = trim($_POST['product_name']);
            $description = trim($_POST['description']);
            $price = $_POST['price'];
            $quantity = $_POST['quantity'];
            // Có thể bổ sung cập nhật category_id, brand_id, image_url nếu cần
            if (!empty($product_id) && !empty($name) && $price > 0 && $quantity >= 0) {
                $sql = "UPDATE products SET name = :name, description = :description, price = :price, quantity = :quantity WHERE product_id = :product_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':price', $price, PDO::PARAM_STR);
                $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $message = "Cập nhật sản phẩm thành công!";
                    $message_type = "success";
                } else {
                    $message = "Lỗi khi cập nhật sản phẩm!";
                    $message_type = "danger";
                }
                $stmt = null;
            } else {
                $message = "Vui lòng nhập thông tin sản phẩm hợp lệ!";
                $message_type = "warning";
            }
        }
    } catch (PDOException $e) {
        $message = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        $message_type = "danger";
    } catch (Exception $e) {
        $message = "Đã xảy ra lỗi: " . $e->getMessage();
        $message_type = "danger";
    }
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>
        
        <div class="col-md-9 col-lg-10">
            <div class="main-content">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i><?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php
                switch ($active_tab) {
                    case 'dashboard':
                        require_once 'pages/dashboard.php';
                        break;
                    case 'users':
                        require_once 'pages/users.php';
                        break;
                    case 'categories':
                        require_once 'pages/categories.php';
                        break;
                    case 'brands':
                        require_once 'pages/brands.php';
                        break;
                    case 'products':
                        require_once 'pages/products.php';
                        break;
                    case 'insert_products':
                        require_once 'pages/insert_products.php';
                        break;
                    case 'orders':
                        require_once 'pages/orders.php';
                        break;
                    default:
                        require_once 'pages/dashboard.php';
                        break;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php $db->close(); ?>
</body>
</html>
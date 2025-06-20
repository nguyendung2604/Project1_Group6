<?php
require_once "db_connect.php";

// Validate input function
function validate_login($username, $password) {
    $errors = [];
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    return $errors;
}

// Xử lý dữ liệu từ form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $errors = validate_login($username, $password);

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Khởi tạo session
                session_start();
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['cart'] = [];

                // Lấy hoặc tạo giỏ hàng
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

                $user_id = $_SESSION['user_id'];
                $cart_id = getOrCreateCartId($pdo, $user_id);
                $stmt = $pdo->prepare("SELECT product_id, quantity FROM cart_items WHERE cart_id = ?");
                $stmt->execute([$cart_id]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($items) {
                    foreach ($items as $item) {
                        $_SESSION['cart'][$item['product_id']] = $item['quantity'];
                    }
                }

                if ($user['role'] === 'admin') {
                    header("Location: ../admin/index.php");
                    exit;
                } else {
                    header("Location: index.php");
                    exit;
                }
            } else {
                $errors[] = "Incorrect login information!";
            }
        } catch (PDOException $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Wireless World</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div style="height: 80px;"></div>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="text-center text-dark mb-4">
                    <h2>Login</h2>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form action="login.php" method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" placeholder="Username" name="username" required>
                                <label for="username">Username</label>
                            </div>
                            
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                                <label for="password">Password</label>
                                <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent toggle-password">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </div>
                            
                            <button class="btn btn-primary w-100 py-2 mb-3" type="submit">LOGIN</button>
                            <div class="text-center">
                                <a href="register.php" class="text-decoration-none">Register</a>
                            </div>
                        </form>

                        <?php if (!empty($errors)): ?>
                            <div class='alert alert-danger text-center'>
                                <?php foreach ($errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
// Kết nối database
require 'db_connect.php';

$product_id = (int) ($_GET['id'] ?? 0);

if (!isset($_SESSION['compare'])) {
    $_SESSION['compare'] = [];
}

// Nếu sản phẩm đã có trong danh sách thì không thêm lại
if (in_array($product_id, $_SESSION['compare'])) {
    header("Location: compare.php");
    exit;
}

// Giới hạn tối đa 4 sản phẩm
if (count($_SESSION['compare']) >= 4) {
    $_SESSION['compare_message'] = "Chỉ có thể so sánh tối đa 4 sản phẩm.";
    header("Location: compare.php");
    exit;
}

$_SESSION['compare'][] = $product_id;
header("Location: compare.php");
exit;
?>
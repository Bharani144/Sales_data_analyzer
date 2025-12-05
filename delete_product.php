<?php
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

try {
    // Check if product has sales
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sales WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $sales_count = $stmt->fetchColumn();
    
    if ($sales_count > 0) {
        header("Location: products.php?error=Cannot delete product with existing sales");
        exit();
    }
    
    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    
    header("Location: products.php?success=Product deleted successfully");
    exit();
} catch (PDOException $e) {
    header("Location: products.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
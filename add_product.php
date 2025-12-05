<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO products (product_name, price, category, description) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_name, $price, $category, $description]);
        
        header("Location: products.php?success=Product added successfully");
        exit();
    } catch (PDOException $e) {
        header("Location: products.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: products.php");
    exit();
}
?>
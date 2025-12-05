<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $sale_date = $_POST['sale_date'];
    $quantity = $_POST['quantity'];
    $total_amount = $_POST['total_amount'];
    $customer_name = $_POST['customer_name'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO sales (product_id, sale_date, quantity, total_amount, customer_name) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $sale_date, $quantity, $total_amount, $customer_name]);
        
        header("Location: sales.php?success=Sale added successfully");
        exit();
    } catch (PDOException $e) {
        header("Location: sales.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: sales.php");
    exit();
}
?>
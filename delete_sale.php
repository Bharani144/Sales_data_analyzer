<?php
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: sales.php");
    exit();
}

$sale_id = $_GET['id'];

try {
    $stmt = $conn->prepare("DELETE FROM sales WHERE sale_id = ?");
    $stmt->execute([$sale_id]);
    
    header("Location: sales.php?success=Sale deleted successfully");
    exit();
} catch (PDOException $e) {
    header("Location: sales.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
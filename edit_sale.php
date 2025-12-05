<?php
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: sales.php");
    exit();
}

$sale_id = $_GET['id'];

// Fetch sale details
$stmt = $conn->prepare("
    SELECT s.*, p.product_name, p.price
    FROM sales s
    JOIN products p ON s.product_id = p.product_id
    WHERE s.sale_id = ?
");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    header("Location: sales.php?error=Sale not found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $sale_date = $_POST['sale_date'];
    $quantity = $_POST['quantity'];
    $total_amount = $_POST['total_amount'];
    $customer_name = $_POST['customer_name'];
    
    try {
        $stmt = $conn->prepare("UPDATE sales SET 
                               product_id = ?, sale_date = ?, quantity = ?, 
                               total_amount = ?, customer_name = ?
                               WHERE sale_id = ?");
        $stmt->execute([$product_id, $sale_date, $quantity, $total_amount, $customer_name, $sale_id]);
        
        header("Location: sales.php?success=Sale updated successfully");
        exit();
    } catch (PDOException $e) {
        header("Location: edit_sale.php?id=$sale_id&error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sale - Sales Data Analyzer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Edit Sale</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="saleDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="saleDate" name="sale_date" 
                                       value="<?php echo htmlspecialchars($sale['sale_date']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="product" class="form-label">Product</label>
                                <select class="form-select" id="product" name="product_id" required>
                                    <option value="">Select Product</option>
                                    <?php
                                    $stmt = $conn->query("SELECT product_id, product_name, price FROM products");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = ($row['product_id'] == $sale['product_id']) ? 'selected' : '';
                                        echo "<option value='{$row['product_id']}' data-price='{$row['price']}' $selected>
                                            {$row['product_name']} (₹{$row['price']})
                                        </option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="<?php echo htmlspecialchars($sale['quantity']); ?>" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="totalAmount" class="form-label">Total Amount</label>
                                <input type="number" step="0.01" class="form-control" id="totalAmount" 
                                       name="total_amount" value="<?php echo htmlspecialchars($sale['total_amount']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="customerName" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customerName" name="customer_name" 
                                       value="<?php echo htmlspecialchars($sale['customer_name']); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Sale</button>
                            <a href="sales.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate total amount when product or quantity changes
        document.getElementById('product').addEventListener('change', calculateTotal);
        document.getElementById('quantity').addEventListener('input', calculateTotal);

        function calculateTotal() {
            const productSelect = document.getElementById('product');
            const quantity = document.getElementById('quantity').value;
            const totalAmount = document.getElementById('totalAmount');
            
            if (productSelect.selectedIndex > 0 && quantity > 0) {
                const price = productSelect.options[productSelect.selectedIndex].getAttribute('data-price');
                totalAmount.value = (price * quantity).toFixed(2);
            }
        }
    </script>
</body>
</html>
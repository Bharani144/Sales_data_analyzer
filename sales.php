<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - Sales Data Analyzer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Sales</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                        <i class="bi bi-plus-circle"></i> Add Sale
                    </button>
                </div>

                <?php
                if (isset($_GET['success'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
                }
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
                }
                ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Customer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->query("
                                SELECT s.sale_id, s.sale_date, p.product_name, s.quantity, 
                                       s.total_amount, s.customer_name
                                FROM sales s
                                JOIN products p ON s.product_id = p.product_id
                                ORDER BY s.sale_date DESC
                            ");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>
                                    <td>{$row['sale_id']}</td>
                                    <td>" . date('d M Y', strtotime($row['sale_date'])) . "</td>
                                    <td>{$row['product_name']}</td>
                                    <td>{$row['quantity']}</td>
                                    <td>₹" . number_format($row['total_amount'], 2) . "</td>
                                    <td>{$row['customer_name']}</td>
                                    <td>
                                        <a href='edit_sale.php?id={$row['sale_id']}' class='btn btn-sm btn-warning'>
                                            <i class='bi bi-pencil'></i>
                                        </a>
                                        <a href='delete_sale.php?id={$row['sale_id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>
                                            <i class='bi bi-trash'></i>
                                        </a>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Sale Modal -->
    <div class="modal fade" id="addSaleModal" tabindex="-1" aria-labelledby="addSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSaleModalLabel">Add New Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="add_sale.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="saleDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="saleDate" name="sale_date" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="product" class="form-label">Product</label>
                            <select class="form-select" id="product" name="product_id" required>
                                <option value="">Select Product</option>
                                <?php
                                $stmt = $conn->query("SELECT product_id, product_name, price FROM products");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['product_id']}' data-price='{$row['price']}'>
                                        {$row['product_name']} (₹{$row['price']})
                                    </option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="totalAmount" class="form-label">Total Amount</label>
                            <input type="number" step="0.01" class="form-control" id="totalAmount" name="total_amount" readonly required>
                        </div>
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customerName" name="customer_name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Sale</button>
                    </div>
                </form>
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
            } else {
                totalAmount.value = '';
            }
        }
    </script>
</body>
</html>
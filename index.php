<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Data Analyzer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card { transition: transform 0.3s; }
        .card:hover { transform: scale(1.03); }
        .sidebar { min-height: 100vh; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="index.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="products.php">
                                <i class="bi bi-box-seam me-2"></i>Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="sales.php">
                                <i class="bi bi-cart me-2"></i>Sales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="reports.php">
                                <i class="bi bi-graph-up me-2"></i>Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2">Dashboard</h1>
                
                <div class="row my-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Products</h5>
                                <?php
                                    $stmt = $conn->query("SELECT COUNT(*) FROM products");
                                    $count = $stmt->fetchColumn();
                                    echo "<h2 class='card-text'>$count</h2>";
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Sales</h5>
                                <?php
                                    $stmt = $conn->query("SELECT COUNT(*) FROM sales");
                                    $count = $stmt->fetchColumn();
                                    echo "<h2 class='card-text'>$count</h2>";
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <?php
                                    $stmt = $conn->query("SELECT SUM(total_amount) FROM sales");
                                    $total = $stmt->fetchColumn();
                                    echo "<h2 class='card-text'>₹" . number_format($total, 2) . "</h2>";
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Recent Sales</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Date</th>
                                        <th>Qty</th>
                                        <th>Amount</th>
                                        <th>Customer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $stmt = $conn->query("
                                            SELECT s.sale_id, p.product_name, s.sale_date, s.quantity, 
                                                   s.total_amount, s.customer_name
                                            FROM sales s
                                            JOIN products p ON s.product_id = p.product_id
                                            ORDER BY s.sale_date DESC
                                            LIMIT 5
                                        ");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr>
                                                <td>{$row['sale_id']}</td>
                                                <td>{$row['product_name']}</td>
                                                <td>" . date('d M Y', strtotime($row['sale_date'])) . "</td>
                                                <td>{$row['quantity']}</td>
                                                <td>₹" . number_format($row['total_amount'], 2) . "</td>
                                                <td>{$row['customer_name']}</td>
                                            </tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
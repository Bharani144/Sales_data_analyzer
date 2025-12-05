<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Sales Data Analyzer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2">Reports</h1>
                
                <div class="row my-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Sales by Product</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="salesByProductChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Monthly Sales</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlySalesChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Detailed Sales Report</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="fromDate" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="fromDate" name="from_date" value="<?php echo isset($_GET['from_date']) ? htmlspecialchars($_GET['from_date']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="toDate" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="toDate" name="to_date" value="<?php echo isset($_GET['to_date']) ? htmlspecialchars($_GET['to_date']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="productFilter" class="form-label">Product</label>
                                <select class="form-select" id="productFilter" name="product_id">
                                    <option value="">All Products</option>
                                    <?php
                                    $stmt = $conn->query("SELECT product_id, product_name FROM products");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = (isset($_GET['product_id']) && $_GET['product_id'] == $row['product_id']) ? 'selected' : '';
                                        echo "<option value='{$row['product_id']}' $selected>{$row['product_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="reports.php" class="btn btn-secondary ms-2">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Amount</th>
                                        <th>Customer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Initialize query
                                    $query = "
                                        SELECT s.sale_date, p.product_name, s.quantity, 
                                               s.total_amount, s.customer_name
                                        FROM sales s
                                        JOIN products p ON s.product_id = p.product_id
                                        WHERE 1=1
                                    ";
                                    
                                    // Add filters if they exist
                                    if (!empty($_GET['from_date'])) {
                                        $query .= " AND s.sale_date >= '{$_GET['from_date']}'";
                                    }
                                    if (!empty($_GET['to_date'])) {
                                        $query .= " AND s.sale_date <= '{$_GET['to_date']}'";
                                    }
                                    if (!empty($_GET['product_id'])) {
                                        $query .= " AND s.product_id = {$_GET['product_id']}";
                                    }
                                    
                                    $query .= " ORDER BY s.sale_date DESC";
                                    
                                    // Execute query
                                    $stmt = $conn->query($query);
                                    $hasResults = false;
                                    
                                    // Initialize totals array with default values
                                    $totals = [
                                        'count' => 0,
                                        'total_qty' => 0,
                                        'total_amount' => 0
                                    ];
                                    
                                    // Get totals
                                    $totalQuery = str_replace(
                                        "SELECT s.sale_date, p.product_name, s.quantity, s.total_amount, s.customer_name",
                                        "SELECT COUNT(*) as count, SUM(s.quantity) as total_qty, SUM(s.total_amount) as total_amount",
                                        $query
                                    );
                                    $totalQuery = preg_replace('/ORDER BY.*/', '', $totalQuery);
                                    $totalStmt = $conn->query($totalQuery);
                                    $totalsResult = $totalStmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($totalsResult) {
                                        $totals = array_merge($totals, $totalsResult);
                                    }
                                    
                                    // Display results
                                    $stmt = $conn->query($query);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $hasResults = true;
                                        echo "<tr>
                                            <td>" . date('d M Y', strtotime($row['sale_date'])) . "</td>
                                            <td>{$row['product_name']}</td>
                                            <td>{$row['quantity']}</td>
                                            <td>₹" . number_format($row['total_amount'], 2) . "</td>
                                            <td>{$row['customer_name']}</td>
                                        </tr>";
                                    }
                                    
                                    if (!$hasResults) {
                                        echo '<tr><td colspan="5" class="text-center">No sales records found for the selected criteria</td></tr>';
                                    }
                                    ?>
                                </tbody>
                                <tfoot class="table-dark">
        <tr>
            <td colspan="2"><strong>Totals</strong></td>
            <td><strong><?php 
                // Calculate total quantity
                $totalQty = 0;
                $stmt->execute(); // Re-execute the query to loop through results again
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $totalQty += $row['quantity'];
                }
                echo $totalQty; 
            ?></strong></td>
            <td><strong>₹<?php 
                // Calculate total amount
                $totalAmount = 0;
                $stmt->execute(); // Re-execute the query
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $totalAmount += $row['total_amount'];
                }
                echo number_format($totalAmount, 2); 
            ?></strong></td>
            <td><strong><?php 
                // Count number of sales
                $salesCount = 0;
                $stmt->execute(); // Re-execute the query
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $salesCount++;
                }
                echo $salesCount; 
            ?> sales</strong></td>
        </tr>
    </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sales by Product Chart
        <?php
        $productSales = $conn->query("
            SELECT p.product_name, SUM(s.quantity) as total_qty, SUM(s.total_amount) as total_amount
            FROM sales s
            JOIN products p ON s.product_id = p.product_id
            GROUP BY p.product_name
            ORDER BY total_amount DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $productLabels = [];
        $productData = [];
        foreach ($productSales as $sale) {
            $productLabels[] = $sale['product_name'];
            $productData[] = $sale['total_amount'];
        }
        ?>
        
        const productCtx = document.getElementById('salesByProductChart').getContext('2d');
        const salesByProductChart = new Chart(productCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($productLabels); ?>,
                datasets: [{
                    label: 'Total Sales Amount (₹)',
                    data: <?php echo json_encode($productData); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Monthly Sales Chart
        <?php
        $monthlySales = $conn->query("
            SELECT DATE_FORMAT(sale_date, '%Y-%m') as month, 
                   SUM(total_amount) as total_amount
            FROM sales
            GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
            ORDER BY month
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $monthLabels = [];
        $monthData = [];
        foreach ($monthlySales as $sale) {
            $monthLabels[] = date('M Y', strtotime($sale['month'] . '-01'));
            $monthData[] = $sale['total_amount'];
        }
        ?>
        
        const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlySalesChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthLabels); ?>,
                datasets: [{
                    label: 'Monthly Sales (₹)',
                    data: <?php echo json_encode($monthData); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
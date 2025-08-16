<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['SESS_USER_ID']) || trim($_SESSION['SESS_USER_ID']) === '') {
    header("Location: ../access-denied.php");
    exit();
}

if ((int) ($_SESSION['SESS_IS_ADMIN'] ?? 0) !== 1) {
    header("Location: ../access-denied.php");
    exit();
}

// Include database connection
require_once '../database/db.php';

// Get dashboard statistics
$stats = [];

// Total products
$result = $conn->query("SELECT COUNT(*) as total FROM product WHERE deleted_at IS NULL");
$stats['products'] = $result->fetch_assoc()['total'];

// Total brands
$result = $conn->query("SELECT COUNT(*) as total FROM brand WHERE deleted_at IS NULL");
$stats['brands'] = $result->fetch_assoc()['total'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as total FROM `order`");
$stats['orders'] = $result->fetch_assoc()['total'];

// Total revenue
$result = $conn->query("SELECT SUM(line_total) as total FROM `order`");
$stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent orders
$recent_orders = $conn->query("
    SELECT o.order_id, o.line_total, o.qty, o.created_at, 
           p.product_name, b.brand_name, u.user_name
    FROM `order` o
    JOIN product_band pb ON o.product_brand_id = pb.product_id
    JOIN product p ON pb.product_id = p.product_id
    JOIN brand b ON pb.brand_id = b.brand_id
    JOIN recepties r ON o.recepties_id = r.recepties_id
    JOIN user u ON r.user_id = u.user_id
    ORDER BY o.created_at DESC
    LIMIT 5
");

// Top selling products
$top_products = $conn->query("
    SELECT p.product_name, b.brand_name, SUM(o.qty) as total_sold, SUM(o.line_total) as total_revenue
    FROM `order` o
    JOIN product_band pb ON o.product_brand_id = pb.product_id
    JOIN product p ON pb.product_id = p.product_id
    JOIN brand b ON pb.brand_id = b.brand_id
    GROUP BY p.product_id, b.brand_id
    ORDER BY total_sold DESC
    LIMIT 5
");

// Include the header file which contains the complete HTML structure
include '../admin/layouts/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Page content -->

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1 class="welcome-title">Welcome back, Admin! 👋</h1>
            <p class="welcome-subtitle">Here's what's happening with your store today</p>
        </div>
        <div class="welcome-actions">
            <a href="products.php" class="btn btn-primary btn-modern">
                <i class="bi bi-plus-circle me-2"></i>Add Product
            </a>
            <a href="../" class="btn btn-outline-light btn-modern">
                <i class="bi bi-eye me-2"></i>View Store
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($_SESSION['MSGS']) && is_array($_SESSION['MSGS'])): ?>
        <div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <ul class="list-unstyled mb-0">
                <?php foreach ($_SESSION['MSGS'] as $msg): ?>
                    <li><?= htmlspecialchars($msg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['MSGS']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR'])): ?>
        <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>Oh no!</strong> Please fix the following errors and try again.
            <ul class="list-unstyled mb-0 mt-2">
                <?php foreach ($_SESSION['ERRMSG_ARR'] as $msg): ?>
                    <li><?= htmlspecialchars($msg) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['ERRMSG_ARR']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="bi bi-box"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number"><?= number_format($stats['products']) ?></h3>
                <p class="stat-label">Total Products</p>
            </div>
            <div class="stat-trend">
                <span class="trend-up">+12%</span>
            </div>
        </div>

        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">$<?= number_format($stats['revenue'], 2) ?></h3>
                <p class="stat-label">Total Revenue</p>
            </div>
            <div class="stat-trend">
                <span class="trend-up">+8%</span>
            </div>
        </div>

        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="bi bi-cart"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number"><?= number_format($stats['orders']) ?></h3>
                <p class="stat-label">Total Orders</p>
            </div>
            <div class="stat-trend">
                <span class="trend-up">+15%</span>
            </div>
        </div>

        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="bi bi-award"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number"><?= number_format($stats['brands']) ?></h3>
                <p class="stat-label">Total Brands</p>
            </div>
            <div class="stat-trend">
                <span class="trend-up">+5%</span>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="dashboard-grid">
        <!-- Recent Orders -->
        <div class="dashboard-card orders-card">
            <div class="card-header-modern">
                <div class="card-title">
                    <i class="bi bi-clock-history me-2"></i>
                    <span>Recent Orders</span>
                </div>
                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body-modern">
                <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                    <div class="orders-list">
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-id">#<?= $order['order_id'] ?></div>
                                    <div class="order-customer"><?= htmlspecialchars($order['user_name']) ?></div>
                                </div>
                                <div class="order-product">
                                    <strong><?= htmlspecialchars($order['product_name']) ?></strong>
                                    <small><?= htmlspecialchars($order['brand_name']) ?></small>
                                </div>
                                <div class="order-details">
                                    <span class="order-qty"><?= $order['qty'] ?>x</span>
                                    <span class="order-total">$<?= number_format($order['line_total'], 2) ?></span>
                                </div>
                                <div class="order-date">
                                    <?= date('M d', strtotime($order['created_at'])) ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-cart-x"></i>
                        <p>No orders yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products -->
        <div class="dashboard-card products-card">
            <div class="card-header-modern">
                <div class="card-title">
                    <i class="bi bi-trophy me-2"></i>
                    <span>Top Products</span>
                </div>
            </div>
            <div class="card-body-modern">
                <?php if ($top_products && $top_products->num_rows > 0): ?>
                    <div class="products-list">
                        <?php while ($product = $top_products->fetch_assoc()): ?>
                            <div class="product-item">
                                <div class="product-info">
                                    <h6><?= htmlspecialchars($product['product_name']) ?></h6>
                                    <small><?= htmlspecialchars($product['brand_name']) ?></small>
                                </div>
                                <div class="product-stats">
                                    <div class="product-sales">
                                        <span class="sales-count"><?= $product['total_sold'] ?></span>
                                        <small>sold</small>
                                    </div>
                                    <div class="product-revenue">
                                        $<?= number_format($product['total_revenue'], 2) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-box"></i>
                        <p>No sales data yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="dashboard-card">
            <div class="card-header-modern">
                <div class="card-title">
                    <i class="bi bi-lightning me-2"></i>
                    <span>Quick Actions</span>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="actions-grid">
                    <a href="products.php" class="action-card action-primary">
                        <div class="action-icon">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <span>Add Product</span>
                    </a>
                    <a href="category.php" class="action-card action-success">
                        <div class="action-icon">
                            <i class="bi bi-tags"></i>
                        </div>
                        <span>Categories</span>
                    </a>
                    <a href="brand_list.php" class="action-card action-info">
                        <div class="action-icon">
                            <i class="bi bi-award"></i>
                        </div>
                        <span>Brands</span>
                    </a>
                    <a href="orders.php" class="action-card action-warning">
                        <div class="action-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <span>Orders</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>
</div>
<!-- footer -->
<?php include '../admin/layouts/footer.php'; ?>

</body>

</html>
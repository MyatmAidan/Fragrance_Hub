<?php
require_once '../auth.php';

// Require login for this page
$auth->requireLogin();

// Get current user data
$currentUser = $auth->getCurrentUser();

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

// Get order details
$order_query = "
    SELECT r.recepties_id, r.date, r.total,
           o.order_id, o.qty, o.line_total, o.payment_method_id,
           p.product_name, p.description, p.gender,
           b.brand_name, pb.price,
           pm.name_of_method as payment_method
    FROM recepties r
    JOIN `order` o ON r.recepties_id = o.recepties_id
    JOIN product_brand pb ON o.product_brand_id = pb.product_brand_id
    JOIN product p ON pb.product_id = p.product_id
    JOIN brand b ON pb.brand_id = b.brand_id
    LEFT JOIN payment_method pm ON o.payment_method_id = pm.payment_method_id
    WHERE r.recepties_id = ? AND r.user_id = ?
    ORDER BY o.order_id
";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $currentUser['user_id']);
$stmt->execute();
$order_items = $stmt->get_result();

if ($order_items->num_rows === 0) {
    header('Location: orders.php');
    exit();
}

// Get order summary
$summary_query = "
    SELECT r.recepties_id, r.date, r.total,
           COUNT(o.order_id) as item_count
    FROM recepties r
    LEFT JOIN `order` o ON r.recepties_id = o.recepties_id
    WHERE r.recepties_id = ? AND r.user_id = ?
    GROUP BY r.recepties_id
";
$stmt = $conn->prepare($summary_query);
$stmt->bind_param("ii", $order_id, $currentUser['user_id']);
$stmt->execute();
$order_summary = $stmt->get_result()->fetch_assoc();

require './layout/user_header.php';
?>


<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-success shadow-dark border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">
                            <i class="material-symbols-rounded me-2">check_circle</i>
                            Order Confirmed!
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Order #<?= $order_summary['recepties_id'] ?></h6>
                            <p class="text-sm mb-0">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order_summary['date'])) ?></p>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <span class="badge badge-sm bg-gradient-success">Confirmed</span>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Brand</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quantity</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Price</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $order_items->fetch_assoc()) { ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                    <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($item['gender']) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($item['brand_name']) ?></p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-info"><?= $item['qty'] ?></span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">$<?= number_format($item['price'], 2) ?></span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">$<?= number_format($item['line_total'], 2) ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="mb-3">Order Summary</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-sm">Items (<?= $order_summary['item_count'] ?>):</span>
                                        <span class="text-sm">$<?= number_format($order_summary['total'], 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-sm">Shipping:</span>
                                        <span class="text-sm">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-sm">Tax:</span>
                                        <span class="text-sm">$0.00</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="font-weight-bold">Total:</span>
                                        <span class="font-weight-bold">$<?= number_format($order_summary['total'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="mb-3">Payment Information</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-sm">Payment Method:</span>
                                        <span class="text-sm"><?= htmlspecialchars($item['payment_method'] ?? 'N/A') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-sm">Status:</span>
                                        <span class="text-sm text-success">Paid</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-sm">Order Date:</span>
                                        <span class="text-sm"><?= date('M d, Y', strtotime($order_summary['date'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>What's Next?</h6>
                </div>
                <div class="card-body p-3">
                    <div class="timeline timeline-one-side">
                        <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="material-symbols-rounded text-success text-gradient">check_circle</i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Order Confirmed</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">Your order has been received</p>
                            </div>
                        </div>
                        <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="material-symbols-rounded text-info text-gradient">inventory</i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Processing</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">We're preparing your order</p>
                            </div>
                        </div>
                        <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="material-symbols-rounded text-warning text-gradient">local_shipping</i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Shipped</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">Your order is on its way</p>
                            </div>
                        </div>
                        <div class="timeline-block">
                            <span class="timeline-step">
                                <i class="material-symbols-rounded text-primary text-gradient">home</i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark text-sm font-weight-bold mb-0">Delivered</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">Enjoy your new fragrance!</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="orders.php" class="btn bg-gradient-dark">
                            <i class="material-symbols-rounded me-1" style="font-size: 1rem;">receipt_long</i>
                            View All Orders
                        </a>
                        <a href="products.php" class="btn btn-outline-primary">
                            <i class="material-symbols-rounded me-1" style="font-size: 1rem;">shopping_bag</i>
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<!--   Core JS Files   -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }

    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            fetch('../auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../login.php';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '../login.php';
                });
        }
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = this.value.trim();
            if (searchTerm) {
                window.location.href = `products.php?search=${encodeURIComponent(searchTerm)}`;
            }
        }
    });
</script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
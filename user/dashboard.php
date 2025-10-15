<?php
require_once '../auth.php';

// Require login for this page
$auth->requireLogin();

// Get current user data
$currentUser = $auth->getCurrentUser();

// Get user's order history
$order_query = "
    SELECT r.recepties_id, r.date, r.total, COUNT(o.order_id) as item_count
    FROM recepties r
    LEFT JOIN `order` o ON r.recepties_id = o.recepties_id
    WHERE r.user_id = ?
    GROUP BY r.recepties_id
    ORDER BY r.date DESC
    LIMIT 5
";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $currentUser['user_id']);
$stmt->execute();
$recent_orders = $stmt->get_result();

// Get total orders count
$count_query = "SELECT COUNT(*) as total_orders FROM recepties WHERE user_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $currentUser['user_id']);
$stmt->execute();
$total_orders = $stmt->get_result()->fetch_assoc()['total_orders'];

// Get total spent
$spent_query = "SELECT SUM(total) as total_spent FROM recepties WHERE user_id = ?";
$stmt = $conn->prepare($spent_query);
$stmt->bind_param("i", $currentUser['user_id']);
$stmt->execute();
$total_spent = $stmt->get_result()->fetch_assoc()['total_spent'] ?? 0;

include './layout/user_header.php';
?>



<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">User Dashboard</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-symbols-rounded opacity-10">shopping_cart</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Orders</p>
                        <h4 class="mb-0"><?= $total_orders ?></h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-success text-sm font-weight-bolder">+100% </span>from last month</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-symbols-rounded opacity-10">attach_money</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Spent</p>
                        <h4 class="mb-0">$<?= number_format($total_spent, 0) ?></h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-success text-sm font-weight-bolder">+5% </span>than last month</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-symbols-rounded opacity-10">check_circle</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Account Status</p>
                        <h4 class="mb-0">Active</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-success text-sm font-weight-bolder">Verified</span> account</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-symbols-rounded opacity-10">store</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Quick Actions</p>
                        <h4 class="mb-0">Shop</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-primary text-sm font-weight-bolder">Browse</span> products</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row mt-4">
        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Recent Orders</h6>
                            <p class="text-sm mb-0">
                                <i class="fa fa-check text-info" aria-hidden="true"></i>
                                <span class="font-weight-bold ms-1"><?= $recent_orders->num_rows ?> orders</span> this month
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Items</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_orders->num_rows > 0) {
                                    while ($order = $recent_orders->fetch_assoc()) { ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">#<?= $order['recepties_id'] ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0"><?= date('M d, Y', strtotime($order['date'])) ?></p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-info"><?= $order['item_count'] ?></span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">$<?= number_format($order['total'], 2) ?></span>
                                            </td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <p class="text-muted">No orders yet. <a href="products.php">Start shopping!</a></p>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <a href="products.php" class="btn btn-primary">
                            <i class="material-symbols-rounded me-2" style="font-size: 1rem;">shopping_bag</i>
                            Browse Products
                        </a>
                        <a href="orders.php" class="btn btn-outline-primary">
                            <i class="material-symbols-rounded me-2" style="font-size: 1rem;">receipt_long</i>
                            View Orders
                        </a>
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="material-symbols-rounded me-2" style="font-size: 1rem;">person</i>
                            Update Profile
                        </a>
                        <a href="cart.php" class="btn btn-outline-primary">
                            <i class="material-symbols-rounded me-2" style="font-size: 1rem;">shopping_cart</i>
                            View Cart
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
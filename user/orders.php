<?php
require_once '../auth.php';

// Require login for this page
$auth->requireLogin();

// Get current user data
$currentUser = $auth->getCurrentUser();

// Get user's orders
$orders_query = "
    SELECT r.recepties_id, r.date, r.total, 
           COUNT(o.order_id) as item_count,
           GROUP_CONCAT(CONCAT(p.product_name, ' (', b.brand_name, ')') SEPARATOR ', ') as products
    FROM recepties r
    LEFT JOIN `order` o ON r.recepties_id = o.recepties_id
    LEFT JOIN product_brand pb ON o.product_brand_id = pb.product_brand_id
    LEFT JOIN product p ON pb.product_id = p.product_id
    LEFT JOIN brand b ON pb.brand_id = b.brand_id
    WHERE r.user_id = ?
    GROUP BY r.recepties_id
    ORDER BY r.date DESC
";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $currentUser['user_id']);
$stmt->execute();
$orders = $stmt->get_result();

include 'layout/user_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Order History</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Your Orders</h6>
                            <p class="text-sm mb-0">Track and manage your orders</p>
                        </div>
                        <div class="col-lg-6 col-5 my-auto text-end">
                            <a href="products.php" class="btn btn-sm bg-gradient-dark">Continue Shopping</a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Items</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-secondary opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders->num_rows > 0) {
                                    while ($order = $orders->fetch_assoc()) { ?>
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
                                                <p class="text-xs text-secondary mb-0"><?= date('h:i A', strtotime($order['date'])) ?></p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-info"><?= $order['item_count'] ?></span>
                                                <p class="text-xs text-secondary mb-0 mt-1"><?= htmlspecialchars(substr($order['products'], 0, 50)) ?>...</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">$<?= number_format($order['total'], 2) ?></span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-success">Completed</span>
                                            </td>
                                            <td class="align-middle">
                                                <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails(<?= $order['recepties_id'] ?>)">
                                                    <i class="material-symbols-rounded" style="font-size: 1rem;">visibility</i>
                                                    View Details
                                                </button>
                                            </td>
                                        </tr>
                                    <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="material-symbols-rounded text-muted mb-3" style="font-size: 4rem;">shopping_cart</i>
                                            <h5 class="text-muted">No orders yet</h5>
                                            <p class="text-muted">Start shopping to see your orders here.</p>
                                            <a href="products.php" class="btn btn-primary">Browse Products</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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

    function viewOrderDetails(orderId) {
        // Show loading
        document.getElementById('orderDetailsContent').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        modal.show();

        // Load order details
        fetch(`order_details.php?id=${orderId}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('orderDetailsContent').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">Error loading order details.</div>';
            });
    }
</script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
<?php
require_once '../auth.php';

// Require login for this page
$auth->requireLogin();

// Get current user data
$currentUser = $auth->getCurrentUser();

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    echo '<div class="alert alert-danger">Invalid order ID.</div>';
    exit;
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
    echo '<div class="alert alert-danger">Order not found or you do not have permission to view this order.</div>';
    exit;
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

?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0">
                <div class="row">
                    <div class="col-lg-6 col-7">
                        <h6>Order #<?= $order_summary['recepties_id'] ?></h6>
                        <p class="text-sm mb-0">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order_summary['date'])) ?></p>
                    </div>
                    <div class="col-lg-6 col-5 my-auto text-end">
                        <span class="badge badge-sm bg-gradient-success">Completed</span>
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
</div>
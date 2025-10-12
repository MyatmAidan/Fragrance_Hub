<?php
require_once '../database/dbrequire.php';

// Set content type for AJAX response
header('Content-Type: text/html; charset=utf-8');

$receipt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($receipt_id <= 0) {
    echo '<div class="alert alert-danger">Invalid receipt ID: ' . $receipt_id . '</div>';
    exit;
}

// Debug: Check if database connection is working
if (!$conn) {
    echo '<div class="alert alert-danger">Database connection failed</div>';
    exit;
}

// Get receipt details
$receipt_query = "
    SELECT r.recepties_id, r.user_id, r.date, r.total, r.created_at,
           u.user_name, u.email, u.phone, u.address
    FROM recepties r
    JOIN user u ON r.user_id = u.user_id
    WHERE r.recepties_id = ?
";
$stmt = $conn->prepare($receipt_query);
if (!$stmt) {
    echo '<div class="alert alert-danger">Database error: ' . $conn->error . '</div>';
    exit;
}

$stmt->bind_param("i", $receipt_id);
$stmt->execute();
$receipt = $stmt->get_result()->fetch_assoc();

if (!$receipt) {
    echo '<div class="alert alert-danger">Receipt not found</div>';
    exit;
}

// Get order items
$orders_query = "
    SELECT o.order_id, o.qty, o.line_total, o.payment_method_id,
           p.product_name, p.description, p.gender,
           b.brand_name, pb.price,
           pm.name_of_method as payment_method
    FROM `order` o
    JOIN product_brand pb ON o.product_brand_id = pb.product_brand_id
    JOIN product p ON pb.product_id = p.product_id
    JOIN brand b ON pb.brand_id = b.brand_id
    LEFT JOIN payment_method pm ON o.payment_method_id = pm.payment_method_id
    WHERE o.recepties_id = ?
    ORDER BY o.order_id
";
$stmt = $conn->prepare($orders_query);
if (!$stmt) {
    echo '<div class="alert alert-danger">Database error: ' . $conn->error . '</div>';
    exit;
}

$stmt->bind_param("i", $receipt_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary">Receipt Information</h6>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>Receipt ID:</strong>
                    </div>
                    <div class="col-sm-8">
                        #<?= $receipt['recepties_id'] ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>Date:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?= date('M d, Y', strtotime($receipt['date'])) ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>Time:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?= date('h:i A', strtotime($receipt['created_at'])) ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>Total Amount:</strong>
                    </div>
                    <div class="col-sm-8">
                        <span class="text-success font-weight-bold">$<?= number_format($receipt['total'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <h6 class="text-primary">Customer Information</h6>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <strong>Name:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?= htmlspecialchars($receipt['user_name']) ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?= htmlspecialchars($receipt['email']) ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>Phone:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?= htmlspecialchars($receipt['phone']) ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4">
                        <strong>Address:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?= htmlspecialchars($receipt['address']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-primary">Order Items</h6>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>Gender</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order) { ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($order['product_name']) ?></strong>
                                        <?php if ($order['description']) { ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($order['description'], 0, 50)) ?>...</small>
                                        <?php } ?>
                                    </td>
                                    <td><?= htmlspecialchars($order['brand_name']) ?></td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-<?= $order['gender'] == 'Male' ? 'primary' : ($order['gender'] == 'Female' ? 'info' : 'secondary') ?>">
                                            <?= htmlspecialchars($order['gender']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= $order['qty'] ?></td>
                                    <td class="text-end">$<?= number_format($order['price'], 2) ?></td>
                                    <td class="text-end"><strong>$<?= number_format($order['line_total'], 2) ?></strong></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Subtotal:</th>
                                <th class="text-end">$<?= number_format($receipt['total'], 2) ?></th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">Tax:</th>
                                <th class="text-end">$0.00</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">Shipping:</th>
                                <th class="text-end">$0.00</th>
                            </tr>
                            <tr class="table-success">
                                <th colspan="5" class="text-end">Total:</th>
                                <th class="text-end">$<?= number_format($receipt['total'], 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($orders)) { ?>
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>Payment Method:</strong> <?= htmlspecialchars($orders[0]['payment_method'] ?? 'Not specified') ?>
            </div>
        </div>
    </div>
<?php } ?>
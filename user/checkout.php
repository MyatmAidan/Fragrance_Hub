<?php
require_once '../auth.php';

// Require login for this page
$auth->requireLogin();

// Get current user data
$currentUser = $auth->getCurrentUser();

// Handle checkout process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'process_checkout') {
        $payment_method_id = (int)($_POST['payment_method_id'] ?? 0);
        $shipping_address  = $_POST['shipping_address'] ?? '';
        $notes             = $_POST['notes'] ?? '';

        if ($payment_method_id <= 0) {
            $message = 'Please select a payment method';
            $message_type = 'danger';
        } else {
            // Pull cart with product_brand_id and current stock
            $cart_query = "
            SELECT c.cart_id, c.quantity, c.product_id, c.brand_id,
                   p.product_name, p.description, p.gender,
                   b.brand_name,
                   pb.product_brand_id, pb.price, pb.Qty AS stock_qty
            FROM cart c
            JOIN product_brand pb ON c.product_id = pb.product_id AND c.brand_id = pb.brand_id
            JOIN product p ON pb.product_id = p.product_id
            JOIN brand b   ON pb.brand_id = b.brand_id
            WHERE c.user_id = ?
            FOR UPDATE
        ";
            $stmt = $conn->prepare($cart_query);
            $stmt->bind_param("i", $currentUser['user_id']);
            $stmt->execute();
            $cart_items = $stmt->get_result();

            if ($cart_items->num_rows > 0) {
                // Compute total & stage items
                $total = 0.0;
                $items = [];
                while ($it = $cart_items->fetch_assoc()) {
                    $line_total = (float)$it['price'] * (int)$it['quantity'];
                    $total += $line_total;
                    $items[] = $it;
                }

                // Start transaction (already locked rows via FOR UPDATE)
                $conn->begin_transaction();
                try {
                    // Create receipt (you can also save shipping_address/notes in a table)
                    $receipt_query = "INSERT INTO recepties (user_id, date, total) VALUES (?, CURDATE(), ?)";
                    $stmt = $conn->prepare($receipt_query);
                    $stmt->bind_param("id", $currentUser['user_id'], $total);
                    $stmt->execute();
                    $receipt_id = $conn->insert_id;

                    // Prepare statements we reuse
                    $dec_stock_sql = "UPDATE product_brand SET Qty = Qty - ? WHERE product_brand_id = ? AND Qty >= ?";
                    $dec_stock_stmt = $conn->prepare($dec_stock_sql);

                    $order_sql = "INSERT INTO `order`
                              (recepties_id, product_brand_id, line_total, qty, payment_method_id)
                              VALUES (?, ?, ?, ?, ?)";
                    $order_stmt = $conn->prepare($order_sql);

                    foreach ($items as $item) {
                        $qtyNeeded = (int)$item['quantity'];
                        $pbid = (int)$item['product_brand_id'];
                        $unitPrice = (float)$item['price'];
                        $lineTotal = $unitPrice * $qtyNeeded;

                        // Atomically decrement stock, preventing negative Qty
                        $dec_stock_stmt->bind_param("iii", $qtyNeeded, $pbid, $qtyNeeded);
                        $dec_stock_stmt->execute();

                        if ($dec_stock_stmt->affected_rows !== 1) {
                            // Not enough stock — rollback and inform user
                            throw new Exception("Insufficient stock for {$item['product_name']} ({$item['brand_name']}).");
                        }

                        // Insert order line
                        $order_stmt->bind_param("iidii", $receipt_id, $pbid, $lineTotal, $qtyNeeded, $payment_method_id);
                        $order_stmt->execute();
                    }

                    // Clear cart
                    $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
                    $stmt = $conn->prepare($clear_cart_query);
                    $stmt->bind_param("i", $currentUser['user_id']);
                    $stmt->execute();

                    // Commit
                    $conn->commit();

                    // Success
                    $message = 'Order placed successfully! Order ID: #' . $receipt_id;
                    $message_type = 'success';
                    header("Location: order_confirmation.php?id=" . $receipt_id);
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = 'Error processing order: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            } else {
                $message = 'Your cart is empty';
                $message_type = 'warning';
            }
        }
    }
}

// Get cart items
$cart_query = "
    SELECT c.cart_id, c.quantity,
           p.product_name, p.description, p.gender,
           b.brand_name, pb.price,
           i.img
    FROM cart c
    JOIN product_brand pb ON c.product_id = pb.product_id AND c.brand_id = pb.brand_id
    JOIN product p ON pb.product_id = p.product_id
    JOIN brand b ON pb.brand_id = b.brand_id
    LEFT JOIN image i ON pb.product_id = i.target_id AND i.type = 'product'
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $currentUser['user_id']);
$stmt->execute();
$cart_items = $stmt->get_result();



// Calculate total
$total = 0;
$cart_data = [];
while ($item = $cart_items->fetch_assoc()) {
    $item_total = $item['price'] * $item['quantity'];
    $total += $item_total;
    $cart_data[] = $item;
}

// Get payment methods
$payment_methods_query = "SELECT payment_method_id, name_of_method FROM payment_method ORDER BY name_of_method";
$payment_methods = $conn->query($payment_methods_query);

// Get user details for shipping
$user_query = "SELECT user_name, email, address, phone FROM user WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $currentUser['user_id']);
$stmt->execute();
$user_details = $stmt->get_result()->fetch_assoc();


require './layout/user_header.php';
?>



<div class="container-fluid py-4">
    <?php if (isset($message)) { ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Checkout</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (count($cart_data) > 0) { ?>
        <form method="POST" id="checkoutForm">
            <input type="hidden" name="action" value="process_checkout">

            <div class="row">
                <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
                    <!-- Shipping Information -->
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Shipping Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <div class="input-group input-group-outline mb-3">
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($user_details['user_name']) ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <div class="input-group input-group-outline mb-3">
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($user_details['email']) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <label class="form-label">Shipping Address</label>
                            <div class="input-group input-group-outline mb-3">
                                <textarea class="form-control" name="shipping_address" rows="3" required><?= htmlspecialchars($user_details['address']) ?></textarea>
                            </div>
                            <label class="form-label">Phone</label>
                            <div class="input-group input-group-outline mb-3">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user_details['phone']) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Payment Information</h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label">Payment Method</label>
                            <div class="input-group input-group-outline mb-3">
                                <select class="form-control" name="payment_method_id" required>
                                    <option value="">Select Payment Method</option>
                                    <?php while ($method = $payment_methods->fetch_assoc()) { ?>
                                        <option value="<?= $method['payment_method_id'] ?>"><?= htmlspecialchars($method['name_of_method']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <label class="form-label">Order Notes (Optional)</label>
                            <div class="input-group input-group-outline mb-3">
                                <textarea class="form-control" name="notes" rows="3" placeholder="Any special instructions for your order..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Order Summary -->
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <h6>Order Summary</h6>
                        </div>
                        <div class="card-body p-3">
                            <!-- Cart Items -->
                            <div class="mb-3">
                                <?php foreach ($cart_data as $item) { ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['img']) { ?>
                                                <img src="../admin/upload/<?= htmlspecialchars($item['img']) ?>"
                                                    class="avatar avatar-sm me-2 border-radius-lg"
                                                    alt="<?= htmlspecialchars($item['product_name']) ?>">
                                            <?php } else { ?>
                                                <div class="avatar avatar-sm me-2 bg-light border-radius-lg d-flex align-items-center justify-content-center">
                                                    <i class="material-symbols-rounded text-muted">image</i>
                                                </div>
                                            <?php } ?>
                                            <div>
                                                <h6 class="text-sm mb-0"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($item['brand_name']) ?></p>
                                                <p class="text-xs text-secondary mb-0">Qty: <?= $item['quantity'] ?></p>
                                            </div>
                                        </div>
                                        <span class="text-sm font-weight-bold">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                    </div>
                                <?php } ?>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-sm">Subtotal:</span>
                                <span class="text-sm">$<?= number_format($total, 2) ?></span>
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
                            <div class="d-flex justify-content-between mb-3">
                                <span class="font-weight-bold">Total:</span>
                                <span class="font-weight-bold">$<?= number_format($total, 2) ?></span>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn bg-gradient-dark" id="placeOrderBtn">
                                    <i class="material-symbols-rounded me-1" style="font-size: 1rem;">payment</i>
                                    Place Order
                                </button>
                                <a href="cart.php" class="btn btn-outline-secondary">
                                    <i class="material-symbols-rounded me-1" style="font-size: 1rem;">arrow_back</i>
                                    Back to Cart
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php } else { ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="material-symbols-rounded text-muted mb-3" style="font-size: 4rem;">shopping_cart</i>
                        <h5 class="text-muted">Your cart is empty</h5>
                        <p class="text-muted">Add some products to your cart to proceed with checkout.</p>
                        <a href="products.php" class="btn bg-gradient-dark">Browse Products</a>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
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

    // Form submission handling
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        placeOrderBtn.disabled = true;
        placeOrderBtn.innerHTML = '<i class="material-symbols-rounded me-1" style="font-size: 1rem;">hourglass_empty</i>Processing...';
    });
</script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
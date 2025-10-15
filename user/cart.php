<?php
require_once '../auth.php';

// Require login for this page
$auth->requireLogin();

// Get current user data
$currentUser = $auth->getCurrentUser();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_to_cart') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $brand_id = (int)($_POST['brand_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($product_id > 0 && $brand_id > 0 && $quantity > 0) {
            // Check if item already exists in cart
            $check_query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND brand_id = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("iii", $currentUser['user_id'], $product_id, $brand_id);
            $stmt->execute();
            $existing_item = $stmt->get_result()->fetch_assoc();

            if ($existing_item) {
                // Update existing item
                $new_quantity = $existing_item['quantity'] + $quantity;
                $update_query = "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE cart_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ii", $new_quantity, $existing_item['cart_id']);
                $stmt->execute();
            } else {
                // Add new item
                $insert_query = "INSERT INTO cart (user_id, product_id, brand_id, quantity) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iiii", $currentUser['user_id'], $product_id, $brand_id, $quantity);
                $stmt->execute();
            }

            echo json_encode(['success' => true, 'message' => 'Product added to cart successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid product data']);
        }
        exit();
    }

    if ($action === 'update_cart') {
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($cart_id > 0 && $quantity > 0) {
            $update_query = "UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE cart_id = ? AND user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("iii", $quantity, $cart_id, $currentUser['user_id']);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Cart updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
        }
        exit();
    }

    if ($action === 'remove_from_cart') {
        $cart_id = (int)($_POST['cart_id'] ?? 0);

        if ($cart_id > 0) {
            $delete_query = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("ii", $cart_id, $currentUser['user_id']);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Item removed from cart successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
        }
        exit();
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
require './layout/user_header.php';
?>



<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Shopping Cart</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (count($cart_data) > 0) { ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="row">
                            <div class="col-lg-6 col-7">
                                <h6>Cart Items</h6>
                                <p class="text-sm mb-0">
                                    <i class="fa fa-shopping-cart text-info" aria-hidden="true"></i>
                                    <span class="font-weight-bold ms-1"><?= count($cart_data) ?> items</span> in your cart
                                </p>
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
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Price</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quantity</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_data as $item) { ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <?php if ($item['img']) { ?>
                                                            <img src="../admin/upload/<?= htmlspecialchars($item['img']) ?>"
                                                                class="avatar avatar-sm me-3 border-radius-lg"
                                                                alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                        <?php } else { ?>
                                                            <div class="avatar avatar-sm me-3 bg-light border-radius-lg d-flex align-items-center justify-content-center">
                                                                <i class="material-symbols-rounded text-muted">image</i>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                        <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($item['brand_name']) ?></p>
                                                        <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($item['gender']) ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($item['brand_name']) ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold" data-unit-price="<?= $item['price'] ?>">$<?= number_format($item['price'], 2) ?></span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(<?= $item['cart_id'] ?>, <?= $item['quantity'] - 1 ?>)">-</button>
                                                    <span class="mx-2" id="qty-<?= $item['cart_id'] ?>"><?= $item['quantity'] ?></span>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(<?= $item['cart_id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold" id="total-<?= $item['cart_id'] ?>">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                            </td>
                                            <td class="align-middle">
                                                <button class="btn btn-outline-danger btn-sm" onclick="removeFromCart(<?= $item['cart_id'] ?>)">
                                                    <i class="material-symbols-rounded" style="font-size: 1rem;">delete</i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6>Order Summary</h6>
                    </div>
                    <div class="card-body p-3">
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
                            <button class="btn bg-gradient-dark" onclick="proceedToCheckout()">
                                <i class="material-symbols-rounded me-1" style="font-size: 1rem;">payment</i>
                                Proceed to Checkout
                            </button>
                            <a href="products.php" class="btn btn-outline-secondary">
                                <i class="material-symbols-rounded me-1" style="font-size: 1rem;">arrow_back</i>
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="material-symbols-rounded text-muted mb-3" style="font-size: 4rem;">shopping_cart</i>
                        <h5 class="text-muted">Your cart is empty</h5>
                        <p class="text-muted">Add some products to your cart to get started!</p>
                        <a href="products.php" class="btn bg-gradient-dark">
                            <i class="material-symbols-rounded me-1" style="font-size: 1rem;">shopping_bag</i>Browse Products
                        </a>
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

    function updateQuantity(cartId, newQuantity) {
        if (newQuantity < 1) {
            removeFromCart(cartId);
            return;
        }

        // Show loading state
        const qtyElement = document.getElementById(`qty-${cartId}`);
        const originalText = qtyElement.textContent;
        qtyElement.textContent = '...';

        fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_cart&cart_id=${cartId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update quantity display
                    qtyElement.textContent = newQuantity;

                    // Update total for this item
                    const unitPriceElement = document.querySelector(`#total-${cartId}`).closest('tr').querySelector('[data-unit-price]');
                    const unitPrice = parseFloat(unitPriceElement.getAttribute('data-unit-price'));
                    const newTotal = unitPrice * newQuantity;
                    document.getElementById(`total-${cartId}`).textContent = '$' + newTotal.toFixed(2);

                    // Update cart summary
                    updateCartSummary();

                    showNotification('Cart updated successfully!', 'success');
                } else {
                    qtyElement.textContent = originalText;
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                qtyElement.textContent = originalText;
                showNotification('An error occurred. Please try again.', 'error');
            });
    }

    function removeFromCart(cartId) {
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            // Show loading state
            const row = document.querySelector(`#qty-${cartId}`).closest('tr');
            row.style.opacity = '0.5';

            fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove_from_cart&cart_id=${cartId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row with animation
                        row.style.transition = 'all 0.3s ease';
                        row.style.transform = 'translateX(-100%)';
                        setTimeout(() => {
                            row.remove();
                            updateCartSummary();

                            // Check if cart is empty
                            const tbody = document.querySelector('tbody');
                            if (tbody.children.length === 0) {
                                location.reload(); // Reload to show empty cart message
                            }
                        }, 300);

                        showNotification('Item removed from cart!', 'success');
                    } else {
                        row.style.opacity = '1';
                        showNotification('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    row.style.opacity = '1';
                    showNotification('An error occurred. Please try again.', 'error');
                });
        }
    }

    function updateCartSummary() {
        // Calculate new totals
        let subtotal = 0;
        const totalElements = document.querySelectorAll('[id^="total-"]');

        totalElements.forEach(element => {
            const price = parseFloat(element.textContent.replace('$', ''));
            subtotal += price;
        });

        // Update summary display
        const subtotalElement = document.querySelector('.card-body .d-flex.justify-content-between span:contains("Subtotal")');
        if (subtotalElement) {
            subtotalElement.textContent = '$' + subtotal.toFixed(2);
        }

        // Update grand total
        const grandTotalElement = document.querySelector('.card-body .d-flex.justify-content-between .font-weight-bold:last-child');
        if (grandTotalElement) {
            grandTotalElement.textContent = '$' + subtotal.toFixed(2);
        }
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }

    function proceedToCheckout() {
        window.location.href = 'checkout.php';
    }
</script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
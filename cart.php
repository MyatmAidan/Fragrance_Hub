<?php
session_start();

// Clear the cart
if (isset($_GET['clear']) && $_GET['clear']) {
    unset($_SESSION['CART']);
    $_SESSION['MSGS'] = array('Your cart has been emptied.');
    session_write_close();
    header("Location: cart.php");
    exit();
}

// Remove an item from the cart
if (isset($_GET['del'])) {
    $del_id = (int)$_GET['del'];
    if (!empty($_SESSION['CART'])) {
        foreach ($_SESSION['CART'] as $cart_item_ID => $cart_item) {
            if ($cart_item['pd_id'] == $del_id) {
                unset($_SESSION['CART'][$cart_item_ID]);
                $_SESSION['MSGS'] = array('Item removed from your cart.');
                session_write_close();
                header("Location: cart.php");
                exit();
            }
        }
    }
}

// Add an item to the cart
if (isset($_GET['add'])) {
    require_once('./database/db.php');

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    if ($mysqli->connect_errno) {
        die("Failed to connect to MySQL: " . $mysqli->connect_error);
    }

    $add_id = (int)$_GET['add'];

    $stmt = $mysqli->prepare("
        SELECT tbl_product.*, tbl_category.cat_name
        FROM tbl_product
        INNER JOIN tbl_category ON tbl_product.cat_id = tbl_category.cat_id
        WHERE pd_id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $add_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $mysqli->close();

    if (!$product) {
        $_SESSION['ERR_MSGS'] = array('Product not found.');
        session_write_close();
        header("Location: cart.php");
        exit();
    }

    if (!isset($_SESSION['CART'])) {
        $_SESSION['CART'] = array();
    }

    // Check if product already in cart by pd_id
    $already_in_cart = false;
    foreach ($_SESSION['CART'] as &$item) {
        if ($item['pd_id'] == $product['pd_id']) {
            $already_in_cart = true;
            break;
        }
    }
    unset($item);

    if (!$already_in_cart) {
        $product['quantity'] = 1; // default quantity 1
        $_SESSION['CART'][] = $product;
        $_SESSION['MSGS'] = array('Item added to your cart.');
    } else {
        $_SESSION['ERR_MSGS'] = array('Item is already added to your cart.');
    }
    session_write_close();
    header("Location: cart.php");
    exit();
}

// Update quantities from form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (!empty($_SESSION['CART']) && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_SESSION['CART'] as &$item) {
            $pd_id = $item['pd_id'];
            if (isset($_POST['quantities'][$pd_id])) {
                $new_qty = (int)$_POST['quantities'][$pd_id];
                if ($new_qty < 1) $new_qty = 1;
                if ($new_qty > $item['pd_qty']) $new_qty = $item['pd_qty'];
                $item['quantity'] = $new_qty;
            }
        }
        unset($item);
        $_SESSION['MSGS'] = array('Cart quantities updated.');
    }
    session_write_close();
    header("Location: cart.php");
    exit();
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<div id="main">
    <header class="container">
        <h3 class="page-header">Cart</h3>
    </header>
    <div class="container">
        <?php
        // Show messages
        if (!empty($_SESSION['MSGS'])) {
            foreach ($_SESSION['MSGS'] as $msg) {
                echo '<div class="alert alert-success">' . htmlspecialchars($msg) . '</div>';
            }
            unset($_SESSION['MSGS']);
        }
        if (!empty($_SESSION['ERR_MSGS'])) {
            foreach ($_SESSION['ERR_MSGS'] as $errmsg) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($errmsg) . '</div>';
            }
            unset($_SESSION['ERR_MSGS']);
        }

        if (!empty($_SESSION['CART'])) {
            $_SESSION['total'] = 0;
        ?>
            <form method="post" action="cart.php">
                <div class="table-responsive">
                    <table class="table products-table">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th class="text-center">Category</th>
                                <th class="text-center" width="90">Quantity</th>
                                <th class="text-center" width="100">Price</th>
                                <th class="text-center" width="100">Subtotal</th>
                                <th class="text-center">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['CART'] as $item) {
                                $qty = isset($item['quantity']) && $item['quantity'] > 0 ? (int)$item['quantity'] : 1;
                                $subtotal = floatval($item['pd_price']) * $qty;
                                $_SESSION['total'] += $subtotal;
                            ?>
                                <tr>
                                    <td>
                                        <img style="max-width:140px;" src="img/uploads/<?php echo htmlspecialchars($item['pd_image']); ?>" alt="<?php echo htmlspecialchars($item['pd_name']); ?>">
                                    </td>
                                    <td><?php echo htmlspecialchars($item['pd_name']); ?></td>
                                    <td><?php echo $item['pd_description'] ? htmlspecialchars($item['pd_description']) : '<span class="text-muted">No description</span>'; ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['cat_name']); ?></td>
                                    <td class="text-center">
                                        <select name="quantities[<?php echo (int)$item['pd_id']; ?>]" required>
                                            <?php
                                            $max_qty = (isset($item['pd_qty']) && (int)$item['pd_qty'] > 0) ? (int)$item['pd_qty'] : 1;
                                            for ($i = 1; $i <= $max_qty; $i++) {
                                                $selected = ($i === $qty) ? 'selected' : '';
                                                echo "<option value=\"$i\" $selected>$i</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td class="text-center">$ <?php echo number_format(floatval($item['pd_price']), 2); ?></td>
                                    <td class="text-center">$ <?php echo number_format($subtotal, 2); ?></td>
                                    <td class="text-center">
                                        <a href="cart.php?del=<?php echo (int)$item['pd_id']; ?>" onclick="return confirm('Are you sure you want to delete this item from your cart?');">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="5"></td>
                                <td>
                                    <h4>Total:</h4>
                                </td>
                                <td colspan="2" class="text-info">
                                    $ <?php echo number_format(floatval($_SESSION['total']), 2); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-right">
                                    <button type="submit" name="update_cart" class="btn btn-primary">Update Quantities</button>
                                    <a href="cart.php?clear=true" class="btn btn-default">Clear Cart <span class="glyphicon glyphicon-shopping-cart"></span></a>
                                    <a href="order.php" class="btn btn-success">Place Order</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php
        } else {
            echo '<div class="alert alert-info">Oh no! Add something to your cart from the Store.</div>';
        }
        ?>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
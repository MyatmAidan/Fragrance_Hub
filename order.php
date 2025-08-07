<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure user is logged in
if (empty($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to place an order.'); window.location.href='login.php';</script>";
    exit();
}

include 'includes/header.php';
include 'includes/nav.php';
?>
<div id="main">
  <header class="container mt-4">
    <h3 class="page-header">Order</h3>
  </header>

  <div class="container mb-5">
    <div class="row">
      <div class="col-md-7 col-sm-6">
        <?php if (!empty($_SESSION['CART']) && is_array($_SESSION['CART'])): ?>
          <h4>Review Order Items</h4>
          <div class="table-responsive">
            <table class="table products-table">
              <thead>
                <tr>
                  <th>Preview</th>
                  <th>Name</th>
                  <th class="text-center">Quantity</th>
                  <th class="text-center">Price</th>
                  <th class="text-center">Remove</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $_SESSION['total'] = 0;
                foreach ($_SESSION['CART'] as $item):
                  $qty = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                  $price = floatval($item['pd_price']);
                  $subtotal = $price * $qty;
                  $_SESSION['total'] += $subtotal;
                ?>
                  <tr>
                    <td><img style="max-width:140px;" src="img/uploads/<?php echo htmlspecialchars($item['pd_image']); ?>" alt="<?php echo htmlspecialchars($item['pd_name']); ?>"></td>
                    <td><?php echo htmlspecialchars($item['pd_name']); ?></td>
                    <td class="text-center"><?php echo $qty; ?></td>
                    <td class="text-center">$ <?php echo number_format($subtotal, 2); ?></td>
                    <td class="text-center">
                      <a href="cart.php?del=<?php echo $item['pd_id']; ?>" onclick="return confirm('Are you sure you want to delete this item from your cart?');">
                        <span class="glyphicon glyphicon-trash"></span>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <tr>
                  <td>&nbsp;</td>
                  <td><h4>Total:</h4></td>
                  <td>&nbsp;</td>
                  <td class="text-info text-center">
                    $ <?php echo number_format($_SESSION['total'], 2); ?>
                  </td>
                  <td>&nbsp;</td>
                </tr>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">Oh no! Add something to your cart from the Store.</div>
        <?php endif; ?>
      </div>

      <div class="col-md-5 col-sm-6">
        <h4>Order Details</h4>
        <form id="order-form" class="form-horizontal" action="includes/order-exec.php" method="POST">
          <div class="form-group">
            <label for="name" class="col-sm-4 control-label">Name</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="name" id="name">
            </div>
          </div>
          <div class="form-group">
            <label for="address" class="col-sm-4 control-label">Address</label>
            <div class="col-sm-8">
              <textarea class="form-control" name="address" id="address" rows="3"></textarea>
            </div>
          </div>
          <div class="form-group">
            <label for="city" class="col-sm-4 control-label">City</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="city" id="city">
            </div>
          </div>
          <div class="form-group">
            <label for="phone_number" class="col-sm-4 control-label">Phone Number</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="phone_number" id="phone_number">
            </div>
          </div>
          <button type="submit" class="btn btn-block btn-success btn-lg">Order &amp; Checkout</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById("order-form").addEventListener("submit", function (e) {
    let missingFields = [];

    ["name", "address", "city", "phone_number"].forEach(id => {
        let field = document.getElementById(id);
        if (!field.value.trim()) {
            missingFields.push(id);
        }
    });

    if (missingFields.length > 0) {
        e.preventDefault();
        alert("Please fill in all required fields.");
        document.getElementById(missingFields[0]).focus();
    }
});
</script>

<?php include 'includes/footer.php'; ?>

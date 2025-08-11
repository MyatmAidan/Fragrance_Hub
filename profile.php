<?php
require_once 'auth.php';
include 'includes/header.php';
include 'includes/nav.php';
include 'includes/profile-data.php';
?>

<div id="main">
	<div class="container">
    <div class="row">
      <!-- User Info Panel -->
      <div class="col-md-5">
        <h4>User credentials</h4>
        <form class="form-horizontal" action="includes/profile-data.php" method="POST">
          <div class="form-group">
            <label for="inputEmail1" class="control-label col-md-4">Username</label>
            <div class="col-md-8">
              <input type="text" value="<?php echo htmlspecialchars($user['user_name']); ?>" class="form-control" disabled>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-4">Email</label>
            <div class="col-md-8">
              <input type="email" value="<?php echo htmlspecialchars($user['user_email']); ?>" class="form-control" disabled>
            </div>
          </div>
          <p class="help-block">Change password</p>

          <?php if (!empty($_SESSION['ERRMSG_ARR'])): ?>
            <div class="alert alert-warning">
              <ul class="list-unstyled">
                <?php foreach ($_SESSION['ERRMSG_ARR'] as $msg): ?>
                  <li><?php echo htmlspecialchars($msg); ?></li>
                <?php endforeach; unset($_SESSION['ERRMSG_ARR']); ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="form-group">
            <label class="control-label col-md-4">Password</label>
            <div class="col-md-8">
              <input type="password" class="form-control" name="password">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-4">Confirm Password</label>
            <div class="col-md-8">
              <input type="password" class="form-control" name="cpassword">
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-offset-4 col-md-8">
              <button type="submit" class="btn btn-default">Change it!</button>
            </div>
          </div>
        </form>
      </div>

      <!-- Orders Panel -->
      <div class="col-md-7">
        <h4>Orders</h4>
        <?php if (!empty($orders)): ?>
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Order Date</th>
                <th>Products</th>
                <th>Order Status</th>
                <th>Order Cost</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td><?php echo (int) $order->od_id; ?></td>
                  <td><?php echo htmlspecialchars($order->od_date); ?></td>
                  <td><?php echo htmlspecialchars($order->products); ?></td>
                  <td>
                    <?php
                    switch ($order->od_status) {
                      case 'New': echo '<span class="label label-primary">New</span>'; break;
                      case 'Shipped': echo '<span class="label label-info">Shipped</span>'; break;
                      case 'Completed': echo '<span class="label label-success">Completed</span>'; break;
                      case 'Cancelled': echo '<span class="label label-danger">Cancelled</span>'; break;
                      default: echo '<span class="label label-default">Processing</span>'; break;
                    }
                    ?>
                  </td>
                  <td>$ <?php echo number_format((float)$order->od_cost, 2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="alert alert-warning">We didn't find any order placed by you.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

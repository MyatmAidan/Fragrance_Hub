<?php 
include_once 'orders-data.php'; 
?>
<div class="col-md-12">
  <?php if (isset($orders) && is_array($orders) && count($orders) > 0): ?>
    <table class="table table-hover products-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Date</th>
          <th>Products</th>
          <th>Status</th>
          <th>Name</th>
          <th>Address</th>
          <th>Cost</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td><?= htmlspecialchars($order->od_id) ?></td>
            <td><?= htmlspecialchars($order->od_date) ?></td>
            <td><?= htmlspecialchars($order->products) ?></td>
            <td><?= htmlspecialchars($order->od_status) ?></td>
            <td><?= htmlspecialchars($order->od_name) ?></td>
            <td>
              <?= htmlspecialchars($order->od_address) ?><br>
              <?= htmlspecialchars($order->od_city) ?> <?= htmlspecialchars($order->od_phone) ?>
            </td>
            <td class="text-center">&#8377; <?= htmlspecialchars($order->od_cost) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning"><strong>Oh my!</strong> Didn't find any orders, please add some.</div>
  <?php endif; ?>
</div><!-- /col-md-12 -->

<?php
session_start();
include 'includes/header.php';
include 'includes/nav.php';
?>

<div id="main">
    <header class="container">
        <h3 class="page-header">Cart</h3>
    </header>
    <div class="container">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <?php
                    $no = 1;
                    $grand_total = 0;
                    foreach ($_SESSION['cart'] as $item):
                        $item_total = $item['price'] * $item['qty'];
                        $grand_total += $item_total;
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <img src="./admin/<?= htmlspecialchars($item['img']) ?>"
                                    style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['qty'] ?></td>
                            <td>$<?= number_format($item_total, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total</strong></td>
                        <td><strong>$<?= number_format($grand_total, 2) ?></strong></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No items in cart</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php //include 'includes/footer.php'; 
?>
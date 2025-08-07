<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['SESS_USER_ID']) || trim($_SESSION['SESS_USER_ID']) === '') {
    header("Location: ../access-denied.php");
    exit();
}

if ((int) ($_SESSION['SESS_IS_ADMIN'] ?? 0) !== 1) {
    header("Location: ../access-denied.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../css/bootstrap.css" />
    <link rel="stylesheet" href="../css/styles.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script src="https://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
</head>
<body>
<div id="main">
    <div class="container">
        <h2>iDukan : Administration</h2>
        <a href="../" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-chevron-left"></span> Back to site</a>
        <hr>
        <?php if (!empty($_SESSION['MSGS']) && is_array($_SESSION['MSGS'])): ?>
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <ul class="list-unstyled">
                    <?php foreach ($_SESSION['MSGS'] as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['MSGS']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR'])): ?>
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>Oh no!</strong> Please fix the following errors and try again.
                <ul class="list-unstyled">
                    <?php foreach ($_SESSION['ERRMSG_ARR'] as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['ERRMSG_ARR']); ?>
        <?php endif; ?>

        <ul class="nav nav-tabs" id="tabs">
            <li class="active"><a data-toggle="tab" href="#category">Categories</a></li>
            <li><a data-toggle="tab" href="#products">Products</a></li>
            <li><a data-toggle="tab" href="#orders">Orders</a></li>
        </ul>

        <div class="tab-content" style="padding-top: 20px;">
            <div class="row tab-pane fade in active" id="category">
                <?php include_once 'category.php'; ?>
            </div>
            <div class="row tab-pane fade" id="products">
                <?php include_once 'products.php'; ?>
            </div>
            <div class="tab-pane fade" id="orders">
                <?php include_once 'orders.php'; ?>
            </div>
        </div>

        <script>
            $(function () {
                //$('#tabs a:last').tab('show')
            })
        </script>
    </div>
</div>
</body>
</html>

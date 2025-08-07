<?php
session_start();

if (!isset($_GET['id'])) {
    header("Location: store.php");
    exit();
} else {
    // Include database connection details
    require_once('./database/db.php');

    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $product = null;

    $stmt = $mysqli->prepare("
        SELECT tbl_product.*, tbl_category.cat_name
        FROM tbl_product
        INNER JOIN tbl_category ON tbl_product.cat_id = tbl_category.cat_id
        WHERE pd_id = ?
    ");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_object();
    }

    $stmt->close();
    $mysqli->close();
}
?>

<?php
include 'includes/header.php';
include 'includes/nav.php';
?>

<div id="main">
    <header class="container">
        <ol class="breadcrumb">
            <li>
                <a href="store.php">Store</a>
            </li>
            <li>
                <a href="store.php?category=<?php echo htmlspecialchars($product->cat_id); ?>">
                    <?php echo htmlspecialchars($product->cat_name); ?>
                </a>
            </li>
            <li class="active"><?php echo htmlspecialchars($product->pd_name); ?></li>
        </ol>
    </header>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <img src="img/uploads/<?php echo htmlspecialchars($product->pd_image); ?>" class="img-responsive">
            </div>
            <div class="col-md-8">
                <h3><?php echo htmlspecialchars($product->pd_name); ?></h3>
                <hr>
                <h4><strong>Price:</strong> &#8377; <?php echo number_format((float)$product->pd_price, 2); ?></h4>
                <p>
                    <?php
                    echo $product->pd_description
                        ? htmlspecialchars($product->pd_description)
                        : '<span class="text-muted">No description</span>';
                    ?>
                </p>
                <p>Available Quantity: <span class="badge"><?php echo (int)$product->pd_qty; ?></span></p>
                <a href="cart.php?add=<?php echo (int)$product->pd_id; ?>" class="btn btn-primary">Add to Cart</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
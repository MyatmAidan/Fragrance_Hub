<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/header.php';
include 'includes/nav.php';
require_once('./database/db.php');

// Connect to database securely
$link = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ($link->connect_error) {
  echo "<div class='alert alert-danger'>Database connection failed: " . htmlspecialchars($link->connect_error) . "</div>";
  exit();
}

$products = [];

if (isset($_GET['search'])) {
  $keyword = trim($_GET['search']);

  if (!empty($keyword)) {
    $stmt = $link->prepare("SELECT `tbl_product`.*, `tbl_category`.`cat_name`
                                FROM `tbl_product`
                                INNER JOIN `tbl_category`
                                ON `tbl_product`.`cat_id` = `tbl_category`.`cat_id`
                                WHERE `pd_name` LIKE CONCAT('%', ?, '%')
                                ORDER BY `pd_id` DESC");
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_object()) {
      $products[] = $row;
    }
    $stmt->close();
  }
} elseif (isset($_GET['category'])) {
  $category = filter_var($_GET['category'], FILTER_VALIDATE_INT);

  if ($category !== false) {
    $stmt = $link->prepare("SELECT `tbl_product`.*, `tbl_category`.`cat_name`
                                FROM `tbl_product`
                                INNER JOIN `tbl_category`
                                ON `tbl_product`.`cat_id` = `tbl_category`.`cat_id`
                                WHERE `tbl_product`.`cat_id` = ?
                                ORDER BY `pd_id` DESC");
    $stmt->bind_param("i", $category);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_object()) {
      $products[] = $row;
    }
    $stmt->close();
  }
} else {
  $query = "SELECT `tbl_product`.*, `tbl_category`.`cat_name`
              FROM `tbl_product`
              INNER JOIN `tbl_category`
              ON `tbl_product`.`cat_id` = `tbl_category`.`cat_id`
              ORDER BY `pd_id` DESC";
  $res = $link->query($query);

  while ($row = $res->fetch_object()) {
    $products[] = $row;
  }
}
?>

<div id="main">
  <header class="container">
    <h3 class="page-header">Store</h3>
  </header>
  <div class="container">
    <div class="row">
      <?php if (!empty($products)) { ?>
        <?php foreach ($products as $product) { ?>
          <div class="col-sm-6 col-md-3">
            <div class="thumbnail">
              <img src="img/uploads/<?php echo htmlspecialchars($product->pd_image); ?>" alt="<?php echo htmlspecialchars($product->pd_name); ?>">
              <div class="caption">
                <h4 class="text-center"><?php echo htmlspecialchars($product->pd_name); ?></h4>
                <p>
                  <a href="product.php?id=<?php echo $product->pd_id; ?>" class="btn btn-default">View</a>
                  <a href="cart.php?add=<?php echo $product->pd_id; ?>" class="btn btn-primary">Add to cart</a>
                </p>
              </div>
            </div>
          </div>
        <?php } ?>
      <?php } else { ?>
        <div class="alert alert-info"><strong>Oh no!</strong> No products found!</div>
      <?php } ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
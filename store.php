<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/header.php';
include 'includes/nav.php';
require_once('./database/db.php');
require_once('./database/central_function.php');

// Select products from the database
$product_sql = select_data('product', $conn, '*');

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div id="main">
  <header class="container">
    <h3 class="page-header">Store</h3>
  </header>
  <div class="container ">
    <div class="mb-3 d-flex flex-row flex-wrap gap-3 justify-content-start" style="display: flex; margin: auto; flex-direction: row; flex-wrap: wrap; gap: 1.5rem; justify-content: center; align-items: stretch; padding: 20px 0;">
      <?php while ($show = $product_sql->fetch_assoc()):
        $product_brand_sql = "SELECT * FROM product_band WHERE product_id = " . (int)$show['product_id'];
        $product_brand_sql = $conn->query($product_brand_sql);
        $product_brand_row = $product_brand_sql ? $product_brand_sql->fetch_assoc() : null;

        $brand_id = null;
        $price = null;
        $qty = 0;
        if ($product_brand_row) {
          $brand_id = $product_brand_row['brand_id'] ?? null;
          $price = $product_brand_row['price'] ?? null;
          $qty = isset($product_brand_row['Qty']) ? (int)$product_brand_row['Qty'] : 0;
        }

        $img_sql = "SELECT img FROM image WHERE type='product' AND target_id='" . $show['product_id'] . "' LIMIT 1";
        $img_result = $conn->query($img_sql);
        $img_path = '';
        if ($img_result && $img_result->num_rows > 0) {
          $img_row = $img_result->fetch_assoc();
          $img_path = $img_row['img'];
        }

        $product_name = $show['product_name'];
        $description = $show['description'];

      ?>
        <div class="card" style="width: 280px; display: flex; flex-direction: column; margin: 10px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.2s ease, box-shadow 0.2s ease; border: none; overflow: hidden; background: white;">
          <div style="padding: 15px; text-align: center; background: #f8f9fa;">
            <?php if ($img_path) { ?>
              <img src="./admin/<?= htmlspecialchars($img_path) ?>" alt="Product Image" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
            <?php } else { ?>
              <div style="width: 100%; height: 200px; background: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                <span class="text-muted">No image</span>
              </div>
            <?php } ?>
          </div>
          <div class="card-body" style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column;">
            <h5 class="card-title" style="font-size: 18px; font-weight: 600; margin-bottom: 10px; color: #333; line-height: 1.3;"><?= htmlspecialchars($product_name) ?></h5>
            <p class="card-text" style="font-size: 14px; color: #666; margin-bottom: 15px; line-height: 1.4; flex-grow: 1;"><?= htmlspecialchars($description) ?></p>
            <div class="product_info d-flex" style="display: flex; gap: 10px; align-items: center; justify-content: space-between; margin-top: auto;">
              <?php if (is_numeric($price)) { ?>
                <p class="card-text" style="font-size: 20px; font-weight: 700; color: #28a745; margin: 0;">$ <?= htmlspecialchars($price) ?></p>
              <?php } else { ?>
                <p class="card-text text-muted" style="font-size: 16px; margin: 0;">Price unavailable</p>
              <?php } ?>
              <button type="button" class="btn btn-primary cart" data-toggle="modal" data-target="#exampleModal"
                data-id="<?= $show['product_id'] ?>"
                data-name="<?= htmlspecialchars($product_name) ?>"
                data-price="<?= htmlspecialchars($price) ?>"
                data-img="<?= htmlspecialchars($img_path) ?>"
                style="padding: 8px 16px; border-radius: 6px; font-size: 14px; transition: all 0.2s ease;"><i class="fa-solid fa-cart-shopping"></i></button>
            </div>


          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add to Cart</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody id="cart-items">
            <!-- Cart items will be inserted here by JS -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" id="add_item" class="btn btn-primary">Add Item</button>
      </div>
    </div>
  </div>
</div>

<?php //include './includes/footer.php'; 
?>

<script>
  $(document).ready(function() {
    let cart = [];
    $('.cart').on('click', function(e) {
      e.preventDefault();
      let name = $(this).data('name');
      let id = $(this).data('id');
      let price = $(this).data('price');
      let img = $(this).data('img');

      let existing = cart.find(item => item.name === name);

      if (existing) {
        existing.qty += 1;
      } else {
        cart.push({
          id: id,
          name: name,
          price: price,
          img: img,
          qty: 1
        });
      }
      console.log(cart);

      cart_update();
      $('#exampleModal').modal('show'); // Show modal after adding
    });

    function cart_update() {
      let cart_table = "";
      let total_cost = 0;

      cart.forEach(item => {

        let itemTotal = item.price * item.qty;
        total_cost += parseFloat(itemTotal.toFixed(2));
        cart_table += `<tr>
          <td>
            <img src="./admin/${item.img}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
            &nbsp;
            ${item.name}
          </td>
          <td>$${item.price}</td>
          <td>
            ${item.qty}
          </td>
          <td class="total-price">${total_cost}</td>
        </tr>`;

      })
      $('#cart-items').html(cart_table);
      $('#cart-total').text(total.toFixed(2));
    }

    $('#add_item').on('click', function(e) {
      e.preventDefault();
      if (cart.length === 0) {
        alert('No items in cart to save.');
        return;
      }
      $.ajax({
        url: 'cart_api.php',
        type: 'POST', // use POST instead of GET for sending JSON
        contentType: "application/json",
        dataType: 'json',
        data: JSON.stringify({
          cart: cart
        }), // send as JSON properly
        success: function(response) {
          if (response.success) {
            alert('Items added to cart successfully!');
          }
        },
        error: function(xhr, status, error) {
          console.error("AJAX Error:", error);
        }
      });
    });



  });
</script>
<?php
require '../database/dbrequire.php';
require '../plugins/common.php';
require '../database/central_function.php';

$brand_res = select_data('brand', $conn, "*", "", "");
$error = false;
$error_message =
    $name_error =
    $name = '';
if (isset($_POST['form_sub']) && $_POST['form_sub'] == '1') {
    $product_id = $_GET['id'];
    $brand_id = $conn->real_escape_string($_POST['brand_id']);
    $price = $conn->real_escape_string($_POST['price']);
    $qty = $conn->real_escape_string($_POST['qty']);


    if ($brand_id === '' || strlen($brand_id) === 0) {
        $error = true;
        $brand_error = "Please select Brand Name.";
    } else if (!is_numeric($brand_id) || intval($brand_id) <= 0) {
        $error = true;
        $brand_error = "Invalid Brand Selected.";
    }

    if ($qty === '' || strlen($qty) === 0) {
        $error = true;
        $qty_error = "Please fill Quantity .";
    } else if (!is_numeric($qty) || intval($qty) < 0) {
        $error = true;
        $qty_error = "Quantity must be a non-negative number.";
    }

    if ($price === '' || strlen($price) === 0) {
        $error = true;
        $price_error = "Please fill Price .";
    } else if (!is_numeric($price) || intval($price) < 0) {
        $error = true;
        $price_error = "Price must be a non-negative number.";
    }

    // Folder path
    $uploadDir =  "upload/";

    // Create folder if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // 0777 full permission, recursive
    }

    $imageName = null;
    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('img_') . "." . strtolower($ext);
        $targetFile = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['img']['tmp_name'], $targetFile)) {
            // ✅ success
        } else {
            $error = true;
            $error_message = "Image upload failed!";
        }
    }

    if (!$error) {
        $data = [
            'product_id' => $_GET['id'],
            'brand_id' => $brand_id,
            'price' => $price,
            'qty' => $qty
        ];

        $data_img = [
            'type' => 'product',
            'target_id' => $_GET['id'],
            'img' => $imageName // save filename to DB
        ];

        $result = insertData('product_brand', $conn, $data);
        $result_img = insertData('image', $conn, $data_img);
        if ($result) {
            $url = $admin_base_url . 'product_list.php?success=Created Success';
            header("Location: $url");
            exit;
        } else {
            $error = true;
            $error_message = "Product Create Fail.";
        }
    }
}
require './layout/header.php';
?>

<!--**********************************
            Content body start
        ***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1>Product Details Create</h1>
            <div class="">
                <a href="<?= $admin_base_url . 'index.php' ?>" class="btn btn-dark">
                    Back
                </a>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <div class="col-md-6 col-sm-10 col-12">
                <?php if ($error && $error_message) { ?>
                    <div class="alert alert-danger">
                        <?= $error_message ?>
                    </div>
                <?php } ?>
                <div class="card">
                    <div class="card-body">
                        <form action="" enctype="multipart/form-data" method="POST">
                            <div class="form-group">
                                <label>Brand</label>
                                <select name="brand_id" class="form-control">
                                    <option value=""> Select Brand </option>
                                    <?php
                                    $brands = $conn->query("SELECT brand_id, brand_name FROM brand");
                                    while ($row = $brands->fetch_assoc()) {
                                        $selected = ($row['brand_id'] == $brand_id) ? 'selected' : '';
                                        echo "<option value='{$row['brand_id']}' {$selected}>{$row['brand_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="" class="form-label">Quantity</label>
                                <input type="text" name="qty" class="form-control" id="qty" value="" />
                                <?php if ($error && $qty_error) { ?>
                                    <span class="text-danger"><?= $qty_error ?></span>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="" class="form-label">Price</label>
                                <input type="text" name="price" class="form-control" id="price" value="" />
                                <?php if ($error && $price_error) { ?>
                                    <span class="text-danger"><?= $price_error ?></span>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="img" class="form-label">Product Image</label>
                                <input type="file" name="img" class="form-control" id="img" accept="image/*" />
                                <?php if ($error && $img_error) { ?>
                                    <span class="text-danger"><?= $img_error ?></span>
                                <?php } ?>
                            </div>
                            <input type="hidden" name="form_sub" value="1" />
                            <button type="submit" class="btn btn-primary w-100">Create</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- #/ container -->
</div>
<!--**********************************
            Content body end
        ***********************************-->

<?php
require './layout/footer.php';

<?php
require '../database/dbrequire.php';
require '../plugins/common.php';
require '../database/central_function.php';


$error = false;
$error_message =
    $name_error =
    $name = '';
if (isset($_POST['form_sub']) && $_POST['form_sub'] == '1') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $gender = $conn->real_escape_string($_POST['gender']);
    // var_dump('hello');
    // die;

    if ($name === '' || strlen($name) === 0) {
        $error = true;
        $name_error = "Please Fill Product Name.";
    } else if (strlen($name) < 3) {
        $error = true;
        $name_error = "Product name must be fill greater then 3.";
    } else if (strlen($name) > 100) {
        $error = true;
        $name_error = "Product name must be fill less then 100.";
    }

    if ($description === '' || strlen($description) === 0) {
        $error = true;
        $description_error = "Please Fill Description.";
    } else if (strlen($description) < 10) {
        $error = true;
        $description_error = "Description must be fill greater then 10.";
    } else if (strlen($description) > 500) {
        $error = true;
        $description_error = "Description must be fill less then 500.";
    }

    if ($gender === '' || strlen($gender) === 0) {
        $error = true;
        $gender_error = "Please Select Gender.";
    } else if ($gender !== 'Male' && $gender !== 'Female') {
        $error = true;
        $gender_error = "Invalid Gender Selected.";
    }

    if (!$error) {

        // normalize to lowercase for checking
        $check_sql = "SELECT product_id FROM product WHERE LOWER(product_name) = LOWER('$name') LIMIT 1";
        $check_res = $conn->query($check_sql);

        if ($check_res && $check_res->num_rows > 0) {
            $error = true;
            $error_message = "Perfume already exists.";
        } else {

            $data = [
                'product_name' => $name,
                'description' => $description,
                'gender' => $gender
            ];
            $result = insertData('product', $conn, $data);

            if ($result) {
                $product_id = $conn->insert_id; // get last inserted product id
                $url = $admin_base_url . "product_detail_create.php?id=$product_id&success=Created Success";
                header("Location: $url");
                exit;
            } else {
                $error = true;
                $error_message = "Product Create Fail.";
            }
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
            <h1>Product Create</h1>
            <div class="">
                <a href="<?= $admin_base_url . 'product_list.php' ?>" class="btn btn-dark">
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
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="name" class="form-label">Perfume Name</label>
                                <input type="text" name="name" class="form-control" id="name" value="<?= htmlspecialchars($name ?? '') ?>" />
                                <?php if (!empty($error) && !empty($name_error)) { ?>
                                    <span class="text-danger"><?= htmlspecialchars($name_error) ?></span>
                                <?php } ?>
                            </div>

                            <div class="form-group mt-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" id="description" value="<?= htmlspecialchars($description ?? '') ?>" />
                                <?php if (!empty($error) && !empty($description_error)) { ?>
                                    <span class="text-danger"><?= htmlspecialchars($description_error) ?></span>
                                <?php } ?>
                            </div>

                            <div class="form-group mt-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="">-- Select Gender --</option>
                                    <option value="Male" <?= (isset($gender) && $gender == 'Male') ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= (isset($gender) && $gender == 'Female') ? 'selected' : '' ?>>Female</option>
                                </select>
                                <?php if (!empty($error) && !empty($gender_error)) { ?>
                                    <span class="text-danger"><?= htmlspecialchars($gender_error) ?></span>
                                <?php } ?>
                            </div>

                            <input type="hidden" name="form_sub" value="1" />
                            <button type="submit" class="btn btn-primary w-100 mt-3">Add Product</button>
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
require './layouts/footer.php';

?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($error && $error_message && !$name_error) { ?>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Duplicate Product Name',
            text: '<?= $error_message ?>',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        })
    </script>
<?php } ?>
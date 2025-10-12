<?php
require_once '../database/dbrequire.php';
require_once '../database/central_function.php';
require_once '../plugins/common.php';

$name = '';
$name_error = '';
$error = false;
$success = false;
if (isset($_POST['form_sub'])) {
    $name = trim(htmlspecialchars($_POST['name']));

    if ($name == '' || strlen($name) < 1) {
        $error = true;
        $name_error = 'Please fill brand name first!';
    } else if (strlen($name) < 2) {
        $error = true;
        $name_error = "Brand name must be fill greater then 2.";
    } else if (strlen($name) > 100) {
        $error = true;
        $name_error = "Brand name must be fill less then 100.";
    }


    if (!$error) {
        $check_sql = "SELECT brand_id FROM brand WHERE LOWER(brand_name) = LOWER('$name') LIMIT 1";
        $check_res = $conn->query($check_sql);
        // var_dump($check_res);
        // die;

        if ($check_res && $check_res->num_rows > 0) {
            $error = true;
            $error_message = "Brand already exists.";
        } else {
            $data = ['brand_name' => $name];
            $result = insertData('brand', $conn, $data);

            if ($result) {
                $url = $admin_base_url . 'brand_list.php?success=Created Success';
                header("Location: $url");
                exit;
            } else {
                $error = true;
                $error_message = "Brand Create Fail.";
            }
        }
    }
}
?>
<?php include './layout/header.php'; ?>


<!--**********************************
            Content body start
        ***********************************-->
<div class="content-body">
    <div class="container-fluid">
        <div class="d-flex justify-content-between">
            <h1>Brand Create</h1>
            <div class="">
                <a href="<?= $admin_base_url . 'brand_list.php' ?>" class="btn btn-dark">
                    Back
                </a>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            <div class="col-md-6 col-sm-10 col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="" class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" id="name" value="<?= htmlspecialchars($name) ?>" />
                                <?php if ($error && $name_error) { ?>
                                    <span class="text-danger"><?= $name_error ?></span>
                                <?php } ?>
                            </div>
                            <input type="hidden" name="form_sub" value="1" />
                            <button type="submit" class="btn btn-primary w-100">Add</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--**********************************
            Content body end
        ***********************************-->
<?php include './layout/footer.php'; ?>
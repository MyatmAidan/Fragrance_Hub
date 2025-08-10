<?php
session_start();

require '../database/db.php';
require '../database/central_function.php';


$error = false;

if (isset($_POST['form_sub']) && $_POST['form_sub'] == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_name = $_POST['brand_name'];

    if (strlen($brand_name) == 0 || $brand_name == '') {
        $error = true;
        $brand_error = 'You must fill brand name.';
    }

    if (!$error) {

        $data = [
            'brand_name' => $brand_name
        ];
        // var_dump($data);
        // die;

        $result = insertData('brand', $conn, $data);
        var_dump($result);
        die;

        if ($result) {
            $url =  '../admin/brand_list.php?success=Created Success';
            header("Location: $url");
            exit;
        } else {
            var_dump("hello");
            $url = '../admin/brand_create.php?error=Error In Insertion';
            header("Location: $url");
            exit;
        }
    }
}

include '../includes/header.php';
include '../includes/nav.php';

?>

<div class="container">
    <div class="row">
        <div class="col-xs-offset-4 col-sm-4 col-sm-offset-4">
            <?php
            if (isset($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR']) && count($_SESSION['ERRMSG_ARR']) > 0) {
            ?>
                <div class="alert alert-warning">
                    <ul class="list-unstyled">
                        <?php
                        foreach ($_SESSION['ERRMSG_ARR'] as $msg) {
                            echo '<li>' . $msg . '</li>';
                        }
                        ?>
                    </ul>
                </div>
            <?php
                unset($_SESSION['ERRMSG_ARR']);
            }

            // Display error messages from URL parameters
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
            }

            if (isset($_GET['success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
            }

            // Display validation errors
            if (isset($brand_error)) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($brand_error) . '</div>';
            }
            ?>
            <form action="brand_create.php" method="post">
                <h3>Brand Create</h3>
                <div class="form-group">
                    <label class="control-label">Brand Name<br></label>
                    <div class="controls">
                        <input type="text" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{1,20}$" class="form-control" placeholder="Brand Name" name="brand_name" maxlength="20">
                    </div>
                </div>
                <input type="hidden" name="form_sub" value="1">
                <button class="btn btn-block btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
<?php
include '../includes/footer.php';
?>
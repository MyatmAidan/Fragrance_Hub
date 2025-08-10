<?php
session_start();

require '../database/db.php';
require '../database/central_function.php';

$error = false;

if (isset($_POST['form_sub']) && $_POST['form_sub'] == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $gender = $_POST['gender'];

    if (strlen($product_name) == 0 || $product_name == '') {
        $error = true;
        $brand_error = 'You must fill product name.';
    }

    if (strlen($description) == 0 || $description == '') {
        $error = true;
        $description_error = 'You must fill product description.';
    }

    if ($brand_name == '') {
        $error = true;
        $gender_error = 'You must choose gender.';
    }

    if (!$error) {

        var_dump("dta");
        die;
        $data = [
            'product_name' => $product_name,
            'description' => $description,
            'gender' => $gender
        ];


        $result = insertData('product', $conn, $data);

        // Insert class image(s)
        $image_success = true;
        $uploaded_files = []; // Track uploaded files

        if ($result && $class_id && isset($_FILES['image'])) {
            $images = $_FILES['image'];
            $allowed = ['JPG', 'jpeg', 'png', 'jpg'];

            // Handle multiple files
            for ($i = 0; $i < count($images['name']); $i++) {
                $tmp = $images['tmp_name'][$i];
                $ext = strtolower(pathinfo($images['name'][$i], PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $folder = "upload/";
                    if (!file_exists($folder)) {
                        mkdir($folder, 0777, true);
                    }
                    $filename = date("Ymd_His") . "_" . uniqid() . "." . $ext;
                    $path = $folder . $filename;

                    if (move_uploaded_file($tmp, $path)) {
                        $uploaded_files[] = $path; // Track file
                        $img_data = [
                            'img' => $path,
                            'type' => 'class',
                            'target_id' => $class_id
                        ];
                        $insert = insertData('image', $conn, $img_data);
                        if (!$insert) {
                            $image_success = false;
                            $name_error = "Image insert failed: " . mysqli_error($conn);
                        }
                    } else {
                        $image_success = false;
                        $name_error = "Failed to move uploaded file.";
                    }
                } else {
                    $image_success = false;
                    $name_error = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
                }
            }
        }

        if ($result) {
            $url = '../admin/product_list.php?success=Brand Created Successfully';
            header("Location: $url");
            exit;
        } else {
            $url = '../admin/product_create.php?error=Error In Insertion';
            header("Location: $url");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Brand - iDukan Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css?v=<?= time() ?>">
</head>

<body class="modern-dashboard">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>

        <!-- Main content -->
        <div class="flex-grow-1">
            <!-- Header -->
            <?php include '../includes/.php'; ?>

            <!-- Page content -->
            <div class="dashboard-content">
                <!-- Page Header -->
                <div class="welcome-section">
                    <div class="welcome-content">
                        <h1 class="welcome-title">Create New Product 🏷️</h1>
                        <p class="welcome-subtitle">Add a new product to your product catalog</p>
                    </div>
                    <div class="welcome-actions">
                        <a href="brand_list.php" class="btn btn-outline-light btn-modern">
                            <i class="bi bi-arrow-left me-2"></i>Back to Brands
                        </a>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR']) && count($_SESSION['ERRMSG_ARR']) > 0): ?>
                    <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($_SESSION['ERRMSG_ARR'] as $msg): ?>
                                <li><?= htmlspecialchars($msg) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['ERRMSG_ARR']); ?>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>

                <!-- Form Card -->
                <div class="dashboard-card">
                    <div class="card-header-modern">
                        <div class="card-title">
                            <i class="bi bi-plus-circle me-2"></i>
                            <span>Brand Information</span>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <form action="product_create.php" method="post" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="product_name" class="form-label fw-bold">
                                            <i class="bi bi-award me-2"></i>Product Name
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="product_name"
                                            name="product_name"
                                            placeholder="Enter product name"
                                            maxlength="50"
                                            pattern="^[a-zA-Z][a-zA-Z0-9-_\.\s]{1,49}$"
                                            required
                                            value="<?= isset($_POST['brand_name']) ? htmlspecialchars($_POST['brand_name']) : '' ?>">

                                        <?php if (isset($product_error)): ?>
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($product_error) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product_name" class="form-label fw-bold">
                                            <i class="bi bi-award me-2"></i>Product Name
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="description"
                                            name="description"
                                            placeholder="Description"
                                            maxlength="100"
                                            required
                                            value="<?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?>">

                                        <?php if (isset($description)): ?>
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($description) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="gender" class="form-label fw-bold">
                                            <i class="bi bi-gender-ambiguous me-2"></i>Gender
                                        </label>
                                        <select
                                            class="form-select form-select-sm"
                                            id="gender"
                                            name="gender"
                                            required>
                                            <option value="" disabled selected>Select gender</option>
                                            <option value="male" <?= (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : '' ?>>Female</option>
                                            <option value="other" <?= (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product_img" class="form-label">
                                            <i class="fas fa-image me-2"></i>Product Images
                                        </label>
                                        <input type="file" name="image[]" multiple class="form-control" id="product_img" accept="image/*">
                                        <div class="form-text">Select one or more images for the class (JPG, PNG only)</div>
                                        <span class="error_msg text-danger"></span>
                                    </div>
                                </div>


                            </div>

                            <!-- <div class="d-flex gap-3 mt-4">
                            </div> -->
                            <input type="hidden" name="form_sub" value="1">
                            <button type="submit" class="btn btn-primary btn-lg btn-modern">
                                <i class="bi bi-check-circle me-2"></i>Create Product
                            </button>
                            <a href="brand_list.php" class="btn btn-outline-secondary btn-lg btn-modern">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>

</html>
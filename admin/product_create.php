<?php
session_start();

require '../database/db.php';
require '../database/central_function.php';

$error = false;

$select_brand = select_data('brand', $conn, '*');

if (isset($_POST['form_sub']) && $_POST['form_sub'] == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $gender = $_POST['gender'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];
    $brand_id = $_POST['brand'];

    if (strlen($product_name) == 0 || $product_name == '') {
        $error = true;
        $brand_error = 'You must fill product name.';
    }

    if (strlen($description) == 0 || $description == '') {
        $error = true;
        $description_error = 'You must fill product description.';
    }

    // Gender Validation
    if (strlen($gender) === 0) {
        $error = true;
        $gender_error = "Gender is require.";
    }

    if (!$error) {
        // Start transaction
        mysqli_begin_transaction($conn);


        try {

            $data = [
                'product_name' => $product_name,
                'description' => $description,
                'gender' => $gender
            ];

            $result = insertData('product', $conn, $data);

            $product_id = mysqli_insert_id($conn);

            $product_barnd_data = [
                'product_id' => $product_id,
                'brand_id' => $brand_id,
                'price' => $price,
                'Qty' => $qty
            ];

            $product_barnd_result = insertData('product_band', $conn, $product_barnd_data);

            // Insert class image(s)
            $image_success = true;
            $uploaded_files = []; // Track uploaded files

            if ($result && $product_id && isset($_FILES['image'])) {
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
                                'type' => 'product',
                                'target_id' => $product_id
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

            // Commit or rollback
            if ($result && $image_success) {
                mysqli_commit($conn);
                $success = "Product inserted successfully!";
                header("Location: product_list.php?success=" . urlencode($success));
                exit;
            } else {
                // Delete uploaded files if transaction fails
                foreach ($uploaded_files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                mysqli_rollback($conn);
                $error = true;
                $name_error = "Database insert failed. Transaction rolled back. MySQL error: " . mysqli_error($conn);
                header("Location: product_create.php?error=" . urlencode($name_error));
                exit;
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = true;
            $description_error = "Error: " . $e->getMessage();
        }
    }
}

?>


<body class="modern-dashboard">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include './layouts/header.php'; ?>

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
                        <a href="index.php" class="btn btn-outline-light btn-modern">
                            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
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
                            <span>Product Information</span>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <form action="product_create.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                                            value="<?= isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : '' ?>">

                                        <?php if (isset($product_error)): ?>
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($product_error) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user me-2"></i>Brand Name
                                        </label>
                                        <select name="brand" class="form-control" id="">
                                            <option value="">Select brand</option>
                                            <?php while ($row = $select_brand->fetch_assoc()): ?>
                                                <option value="<?= $row['brand_id'] ?>"><?= htmlspecialchars($row['brand_name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label fw-bold">
                                            <i class="bi bi-award me-2"></i> Description
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
                                                <?= htmlspecialchars($description_error) ?>
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
                                            <option value="male" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Men') ? 'selected' : '' ?>>Men</option>
                                            <option value="female" <?= (isset($_POST['gender']) && $_POST['gender'] == 'Women') ? 'selected' : '' ?>>Women</option>
                                            <option value="other" <?= (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label fw-bold">
                                            <i class="bi bi-award me-2"></i> Price
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="price"
                                            name="price"
                                            placeholder="price"
                                            required
                                            value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>">

                                        <?php if (isset($price)): ?>
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($price_error) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="qty" class="form-label fw-bold">
                                            <i class="bi bi-award me-2"></i> Quantity
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="qty"
                                            name="qty"
                                            placeholder="Quantity"
                                            required
                                            value="<?= isset($_POST['qty']) ? htmlspecialchars($_POST['qty']) : '' ?>">

                                        <?php if (isset($qty)): ?>
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($qty_error) ?>
                                            </div>
                                        <?php endif; ?>
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
    <!-- footer -->
    <?php include '../admin/layouts/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
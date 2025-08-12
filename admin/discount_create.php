<?php
session_start();

require '../database/db.php';
require '../database/central_function.php';

$error = false;

if (isset($_POST['form_sub']) && $_POST['form_sub'] == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_of_package = $_POST['name_of_package'];
    $percentage = $_POST['percentage'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if (strlen($name_of_package) == 0 || $name_of_package == '') {
        $error = true;
        $discount_error = 'You must fill discount name.';
    }

    if ($percentage == '') {
        $error = true;
        $percentage_error = 'You must fill discount percentage.';
    }


    if (!$error) {
        // Start transaction
        mysqli_begin_transaction($conn);

        try {

            $data = [
                'name_of_package' => $name_of_package
            ];

            $discount_result = insertData('discount', $conn, $data);

            //for discount detail insert
            $discount_id = mysqli_insert_id($conn);

            $discoiunt_info = [
                'discount_id' => $discount_id,
                'percentage' => $percentage,
                'start_date' => $start_date,
                'end_date' => $end_date
            ];

            $discoiunt_info_result = insertData('discount_details', $conn, $discoiunt_info);


            // Commit or rollback
            if ($discount_result && $discoiunt_info_result) {
                mysqli_commit($conn);
                $success = "Discount created successfully!";
                header("Location: index.php?success=" . urlencode($success));
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
                header("Location: discount_create.php?error=" . urlencode($name_error));
                exit;
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = true;
            $discount_error = "Error: " . $e->getMessage();
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

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<body class="modern-dashboard">
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include './layouts/header.php'; ?>

        <!-- Main content -->
        <div class="flex-grow-1">
            <!-- Header -->

            <!-- Page content -->
            <div class="dashboard-content">
                <!-- Page Header -->
                <div class="welcome-section">
                    <div class="welcome-content">
                        <h1 class="welcome-title">Create Discount 🏷️</h1>
                        <p class="welcome-subtitle">Create new discount package to your Fragrance shop</p>
                    </div>
                    <div class="welcome-actions">
                        <a href="index.php" class="btn btn-outline-light btn-modern">
                            <i class="bi bi-arrow-left me-2"></i>Back to dashboard
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
                            <span>Discount Create</span>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <form action="discount_create.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name_of_package" class="form-label fw-bold">
                                            <i class="bi bi-award me-2"></i>Discount Name
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="name_of_package"
                                            name="name_of_package"
                                            placeholder="Enter Discount name"
                                            maxlength="50"
                                            required
                                            value="<?= isset($_POST['name_of_package']) ? htmlspecialchars($_POST['name_of_package']) : '' ?>">

                                        <?php if (isset($dicount_error)): ?>
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($discount_error) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label fw-bold">
                                            <i class="bi bi-award me-2"></i> Percentage
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm"
                                            id="percentage"
                                            name="percentage"
                                            placeholder="percentage"
                                            required
                                            value="<?= isset($_POST['percentage']) ? htmlspecialchars($_POST['percentage']) : '' ?>">

                                        <?php if (isset($percentage)): ?>
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($percentage_error) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <div class="col-md-6 mb-3">
                                            <label for="start_date" class="form-label">
                                                <i class="fas fa-calendar-alt me-2"></i>Start Date
                                            </label>
                                            <input type="text" class="form-control flatpickr-input" id="start_date" name="start_date" value="<?= $start_date ?>" placeholder="Select start date" readonly>
                                            <div class="form-text">Click to select start date</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="join_date" class="form-label">
                                                <i class="fas fa-calendar-alt me-2"></i>End Date
                                            </label>
                                            <input type="text" class="form-control flatpickr-input" id="end_date" name="end_date" value="<?= $end_date ?>" placeholder="Select end date" readonly>
                                            <div class="form-text">Click to select end date</div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <!-- <div class="d-flex gap-3 mt-4">
                            </div> -->
                            <input type="hidden" name="form_sub" value="1">
                            <button type="submit" class="btn btn-primary btn-lg btn-modern">
                                <i class="bi bi-check-circle me-2"></i>Create Discount
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg btn-modern">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flatpickr JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Flatpickr for DOB
        flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            maxDate: "2030-12-31",
            minDate: "2020-01-01",
            allowInput: false,
            clickOpens: true,
            disableMobile: false,
            static: false,
            position: "above"
        });

        // Initialize Flatpickr for Join Date
        flatpickr("#end_date", {
            dateFormat: "Y-m-d",
            maxDate: "2030-12-31",
            minDate: "2020-01-01",
            allowInput: false,
            clickOpens: true,
            disableMobile: false,
            static: false,
            position: "above"
        });

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
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

        $result = insertData('brand', $conn, $data);

        if ($result) {
            $url = '../admin/brand_list.php?success=Brand Created Successfully';
            header("Location: $url");
            exit;
        } else {
            $url = '../admin/brand_create.php?error=Error In Insertion';
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
                        <h1 class="welcome-title">Create New Brand 🏷️</h1>
                        <p class="welcome-subtitle">Add a new brand to your product catalog</p>
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
                        <form action="brand_create.php" method="post" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label for="brand_name" class="form-label fw-bold">
                                            <i class="bi bi-award me-2"></i>Brand Name
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control form-control-lg"
                                            id="brand_name"
                                            name="brand_name"
                                            placeholder="Enter brand name (e.g., Nike, Apple, Samsung)"
                                            maxlength="50"
                                            pattern="^[a-zA-Z][a-zA-Z0-9-_\.\s]{1,49}$"
                                            required
                                            value="<?= isset($_POST['brand_name']) ? htmlspecialchars($_POST['brand_name']) : '' ?>">
                                        <div class="form-text">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Brand name should start with a letter and can contain letters, numbers, hyphens, dots, and spaces.
                                        </div>
                                        <?php if (isset($brand_error)): ?>
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                <?= htmlspecialchars($brand_error) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-lightbulb me-2"></i>Tips
                                            </h6>
                                            <ul class="list-unstyled small mb-0">
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle text-success me-1"></i>
                                                    Use clear, memorable names
                                                </li>
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle text-success me-1"></i>
                                                    Avoid special characters
                                                </li>
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle text-success me-1"></i>
                                                    Keep it under 50 characters
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="d-flex gap-3 mt-4">
                            </div> -->
                            <button type="submit" class="btn btn-primary btn-lg btn-modern">
                                <i class="bi bi-check-circle me-2"></i>Create Brand
                            </button>
                            <a href="brand_list.php" class="btn btn-outline-secondary btn-lg btn-modern">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>

                            <input type="hidden" name="form_sub" value="1">
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
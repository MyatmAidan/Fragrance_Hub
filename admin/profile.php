<?php
require_once '../auth.php';

// Require admin login for this page
$auth->requireAdmin();

// Get current user data
$currentUser = $auth->getCurrentUser();

// Get user details from database
$user_query = "SELECT user_name, email, address, phone, created_at FROM user WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $currentUser['user_id']);
$stmt->execute();
$user_details = $stmt->get_result()->fetch_assoc();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $phone = $_POST['phone'] ?? '';

        $result = $auth->updateProfile($currentUser['user_id'], $name, $email, $address, $phone);

        if ($result['success']) {
            $message = $result['message'];
            $message_type = 'success';
            // Refresh user details
            $user_details = [
                'user_name' => $name,
                'email' => $email,
                'address' => $address,
                'phone' => $phone,
                'created_at' => $user_details['created_at']
            ];
        } else {
            $message = $result['message'];
            $message_type = 'danger';
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($new_password !== $confirm_password) {
            $message = 'New passwords do not match';
            $message_type = 'danger';
        } else {
            $result = $auth->changePassword($currentUser['user_id'], $current_password, $new_password);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
    }
}

require './layout/header.php';
?>

<div class="container-fluid py-4">
    <?php if ($message) { ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <div class="row">
        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Admin Profile Information</h6>
                            <p class="text-sm mb-0">Update your personal information</p>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <form method="POST" class="p-3">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <div class="input-group input-group-outline mb-3">
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user_details['user_name']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <div class="input-group input-group-outline mb-3">
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user_details['email']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <div class="input-group input-group-outline mb-3">
                                    <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user_details['address']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <div class="input-group input-group-outline mb-3">
                                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user_details['phone']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Change Password</h6>
                </div>
                <div class="card-body p-3">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>

                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>

                        <div class="input-group input-group-outline mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Account Information</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex py-1">
                                <div class="my-auto">
                                    <i class="material-symbols-rounded text-success me-3">person</i>
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="text-sm font-weight-normal mb-1">
                                        <span class="font-weight-bold">Member Since</span>
                                    </h6>
                                    <p class="text-xs text-secondary mb-0">
                                        <?= date('F d, Y', strtotime($user_details['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex py-1">
                                <div class="my-auto">
                                    <i class="material-symbols-rounded text-info me-3">admin_panel_settings</i>
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="text-sm font-weight-normal mb-1">
                                        <span class="font-weight-bold">Account Type</span>
                                    </h6>
                                    <p class="text-xs text-secondary mb-0">
                                        Administrator
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--   Core JS Files   -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
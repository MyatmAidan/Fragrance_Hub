<?php
require_once 'auth.php';

// Check if user is logged in
if ($auth->isLoggedIn()) {
    $currentUser = $auth->getCurrentUser();

    // Redirect based on user role
    if ($currentUser['user_role'] == 1) {
        // Admin user
        header('Location: admin/index.php');
    } else {
        // Regular user
        header('Location: user/dashboard.php');
    }
} else {
    // Not logged in, redirect to login
    header('Location: login.php');
}
exit();

<?php
$current_page = basename($_SERVER['PHP_SELF']);
$currentUser = $auth->getCurrentUser();
if ($current_page == 'dashboard.php') {
    $page_title = 'Dashboard';
} elseif ($current_page == 'products.php') {
    $page_title = 'Products';
} elseif ($current_page == 'orders.php') {
    $page_title = 'My Orders';
} elseif ($current_page == 'profile.php') {
    $page_title = 'Profile';
} elseif ($current_page == 'order_details.php') {
    $page_title = 'Order Details';
} elseif ($current_page == 'cart.php') {
    $page_title = 'Shopping Cart';
} elseif ($current_page == 'checkout.php') {
    $page_title = 'Checkout';
} elseif ($current_page == 'order_confirmation.php') {
    $page_title = 'Order Confirmation';
} else {
    $page_title = 'User Dashboard';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>Fragrance Hub - User Dashboard</title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <!-- Nucleo Icons -->
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand px-4 py-3 m-0" href="dashboard.php">
                <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
                <span class="ms-1 text-sm text-dark">Fragrance Hub</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0 mb-2">
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="dashboard.php">
                        <i class="material-symbols-rounded opacity-5">dashboard</i>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'products.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="products.php">
                        <i class="material-symbols-rounded opacity-5">shopping_bag</i>
                        <span class="nav-link-text ms-1">Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'orders.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="orders.php">
                        <i class="material-symbols-rounded opacity-5">receipt_long</i>
                        <span class="nav-link-text ms-1">My Orders</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page == 'profile.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="profile.php">
                        <i class="material-symbols-rounded opacity-5">person</i>
                        <span class="nav-link-text ms-1">Profile</span>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Account</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="#" onclick="logout()">
                        <i class="material-symbols-rounded opacity-5">logout</i>
                        <span class="nav-link-text ms-1">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="dashboard.php">User</a></li>
                        <li class="breadcrumb-item text-sm text-dark active" aria-current="page"><?= $page_title ?></li>
                    </ol>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                        <div class="input-group input-group-outline">
                            <label class="form-label">Search products...</label>
                            <input type="text" class="form-control" id="searchInput">
                        </div>
                    </div>
                    <ul class="navbar-nav d-flex align-items-center mx-3 justify-content-end">
                        <li class="nav-item d-flex mx-2 align-items-center">
                            <a href="cart.php" class="nav-link text-body font-weight-bold px-0">
                                <i class="material-symbols-rounded">shopping_cart</i>
                            </a>
                        </li>
                        <li class="nav-item d-flex mx-1 align-items-center">
                            <a href="profile.php" class="nav-link text-body font-weight-bold px-2">
                                <i class="material-symbols-rounded">account_circle</i>
                                <span class="d-sm-inline d-none"><?= htmlspecialchars($currentUser['user_name']) ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
<?php
include __DIR__ . '/../../require/common.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - iDukan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css?v=<?= time() ?>" />
    <link rel="stylesheet" href="../css/adminstyle.css?v=<?= time() ?>" />
    <link rel="stylesheet" href="../node_modules/metismenujs/dist/metismenujs.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


</head>
<aside>
    <nav id="adminSidebar" class="sidebar">
        <div class="app-brand">
            <a href="" class="app-title">Fragrance_hub</a>
        </div>

        <ul class="sider_menu metismenu">
            <li>
                <a class="" href="index.php">
                    <i class="bi bi-ui-checks icon">
                    </i>
                    <span>Dashboard</span>
                </a>

            </li>
            <li>
                <a class="has-arrow" aria-expanded="false">
                    <i class="bi bi-ui-checks icon">
                    </i>
                    <span>Fragrance_Brand</span>
                </a>
                <ul class="collapse-list">
                    <li id="c_list"><a href="<?= $admin_base_url ?>brand_list.php"><span>Brand_list</span></a></li>
                    <li id="c_list"><a href="<?= $admin_base_url ?>brand_create.php"><span>Brand_create</span></a></li>
                </ul>
            </li>
            <li>
                <a class="has-arrow" aria-expanded="false">
                    <i class="bi bi-archive-fill icon">
                    </i>
                    <span>Product</span>
                </a>
                <ul class="collapse-list">
                    <li id="c_list"><a href="<?= $admin_base_url ?>product_list.php"><span>Product_list</span></a></li>
                    <li id="c_list"><a href="<?= $admin_base_url ?>brand_create.php"><span>Product_create</span></a></li>
                </ul>
            </li>
            <li>
                <a class="has-arrow" aria-expanded="false">
                    <i class="bi bi-percent icon">
                    </i>
                    <span>Discount</span>
                </a>
                <ul class="collapse-list">
                    <li id="c_list"><a href="<?= $admin_base_url ?>discount_list.php"><span>Discount_list</span></a></li>
                    <li id="c_list"><a href="<?= $admin_base_url ?>discount_create.php"><span>Discount_create</span></a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <button class="btn btn-light me-2 sidebar-toggle-btn" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <div class="navbar-brand ms-2">
        <i class="bi bi-shop me-2"></i>
        <strong>iDukan Admin</strong>
    </div>

    <form class="d-none d-md-flex ms-auto me-3">
        <div class="input-group input-group-sm search-group">
            <input class="form-control search-input" type="search" placeholder="Search..." aria-label="Search">
            <button class="btn btn-outline-secondary search-btn" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>

    <div class="d-flex align-items-center">
        <div class="dropdown me-3">
            <button class="btn btn-light position-relative notification-btn" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill notification-badge">
                    3
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end modern-dropdown">
                <li>
                    <h6 class="dropdown-header">Notifications</h6>
                </li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-cart me-2"></i>New order received</a></li>
                <li><a class="dropdown-item" href="#"><i class="dropdown-item" href="#"><i class="bi bi-exclamation-triangle me-2"></i>Product stock low</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-star me-2"></i>Customer review</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View all notifications</a></li>
            </ul>
        </div>

        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle user-menu-btn" id="userMenu" data-bs-toggle="dropdown">
                <img src="https://via.placeholder.com/32" alt="Admin" class="rounded-circle me-2 user-avatar" width="32" height="32">
                <span class="d-none d-sm-inline user-name">Admin</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end modern-dropdown" aria-labelledby="userMenu">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</aside>
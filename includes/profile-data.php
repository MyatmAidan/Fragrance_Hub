<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Include database connection details
require_once('./database/db.php');

if (!isset($_SESSION['SESS_USER_ID'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['SESS_USER_ID'];

// Connect to MySQL
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
    die("Cannot access db: " . mysqli_connect_error());
}

// Fetch user info
$stmt = mysqli_prepare($link, "SELECT * FROM tbl_user WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Fetch user orders
$orders = [];
$order_sql = "
    SELECT tbl_order.*, GROUP_CONCAT(tbl_product.pd_name SEPARATOR ', ') AS products
    FROM tbl_order
    JOIN tbl_order_item ON tbl_order.od_id = tbl_order_item.od_id
    JOIN tbl_product ON tbl_order_item.pd_id = tbl_product.pd_id
    WHERE tbl_order.user_id = ?
    GROUP BY tbl_order.od_id
";
$stmt = mysqli_prepare($link, $order_sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_object($result)) {
    $orders[] = $row;
}
mysqli_stmt_close($stmt);

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';
    $errmsg_arr = [];
    $errflag = false;

    // Validate inputs
    if ($password === '') {
        $errmsg_arr[] = 'Password missing';
        $errflag = true;
    }
    if ($cpassword === '') {
        $errmsg_arr[] = 'Confirm password missing';
        $errflag = true;
    }
    if ($password !== $cpassword) {
        $errmsg_arr[] = 'Passwords do not match';
        $errflag = true;
    }
    if (strlen($password) < 6) {
        $errmsg_arr[] = 'Password is too short.';
        $errflag = true;
    }

    // Handle errors
    if ($errflag) {
        $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
        session_write_close();
        header("Location: ../profile.php");
        exit();
    }

    // Update password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $update_sql = "UPDATE tbl_user SET password = ?, updated_at = NOW() WHERE user_id = ?";
    $stmt = mysqli_prepare($link, $update_sql);
    mysqli_stmt_bind_param($stmt, 'si', $hashed_password, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['MSGS'] = ['<strong>Wola!</strong> Your password was changed successfully.'];
        session_write_close();
        header("Location: ../profile.php");
        exit();
    } else {
        die("Query failed: " . mysqli_error($link));
    }
}

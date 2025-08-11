<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./database/db.php');

// Connect using mysqli
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Ensure user session exists
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Session expired. Please log in again.'); window.location.href='../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$od_date = date('Y-m-d');
$od_name = trim($_POST['name']);
$od_address = trim($_POST['address']);
$od_city = trim($_POST['city']);
$od_phone = trim($_POST['phone_number']);
$od_cost = $_SESSION['total'];

// Insert order details
$stmt = $mysqli->prepare("INSERT INTO tbl_order (user_id, od_date, od_status, od_name, od_address, od_city, od_phone, od_cost)
                          VALUES (?, ?, 'New', ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssd", $user_id, $od_date, $od_name, $od_address, $od_city, $od_phone, $od_cost);
$stmt->execute();
$od_id = $stmt->insert_id;
$stmt->close();

// Insert order items **without price or amount**
foreach ($_SESSION['CART'] as $cart_item) {
    $stmt = $mysqli->prepare("INSERT INTO tbl_order_item (od_id, pd_id, od_qty) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $od_id, $cart_item['pd_id'], $cart_item['pd_qty']);
    $stmt->execute();
    $stmt->close();

    // Update product quantity
    $stmt = $mysqli->prepare("UPDATE tbl_product SET pd_qty = pd_qty - ? WHERE pd_id = ?");
    $stmt->bind_param("ii", $cart_item['pd_qty'], $cart_item['pd_id']);
    $stmt->execute();
    $stmt->close();
}

// Check if order was successfully placed
if ($od_id) {
    unset($_SESSION['CART']);
    $_SESSION['MSGS'] = array('<strong>Success!</strong> Your order has been placed.');
    session_write_close();
    header("location: ../profile.php");
    exit();
} else {
    die("Order placement failed: " . $mysqli->error);
}

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection details
require_once('./database/db.php');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($mysqli->connect_error) {
    die("Cannot access db: " . $mysqli->connect_error);
}

$categories = [];

// Get all the categories with product count
$sql = "SELECT COUNT(tbl_product.cat_id) AS product_count, tbl_category.*
        FROM tbl_category
        LEFT JOIN tbl_product ON tbl_product.cat_id = tbl_category.cat_id
        GROUP BY tbl_category.cat_id";

if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_object()) {
        $categories[] = $row;
    }
    $result->free();
} else {
    die("Query failed: " . $mysqli->error);
}

// Handle new category request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $errmsg_arr = [];
    $errflag = false;

    $catname = trim($_POST['catname'] ?? '');
    $catdesc = trim($_POST['catdesc'] ?? '');

    if ($catname === '') {
        $errmsg_arr[] = 'Category name missing';
        $errflag = true;
    }

    if ($errflag) {
        $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
        session_write_close();
        header("Location: index.php");
        exit();
    }

    // Use prepared statement to insert
    $stmt = $mysqli->prepare("INSERT INTO tbl_category (cat_name, cat_description) VALUES (?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $stmt->bind_param("ss", $catname, $catdesc);

    if ($stmt->execute()) {
        $_SESSION['MSGS'] = ['<strong>Wola!</strong> Changes were successful.'];
        $stmt->close();
        session_write_close();
        header("Location: index.php");
        exit();
    } else {
        $stmt->close();
        die("Query failed: " . $mysqli->error);
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $catid = (int)$_GET['delete'];

    $stmt = $mysqli->prepare("DELETE FROM tbl_category WHERE cat_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $stmt->bind_param("i", $catid);

    if ($stmt->execute()) {
        $_SESSION['MSGS'] = ['<strong>Wola!</strong> Changes were successful.'];
        $stmt->close();
        session_write_close();
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['ERRMSG_ARR'] = ['<strong>Oh no!</strong> Changes didn\'t happen, make sure your database is up.'];
        $stmt->close();
        session_write_close();
        header("Location: index.php");
        exit();
    }
}

$mysqli->close();

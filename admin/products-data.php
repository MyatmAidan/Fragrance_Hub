<?php
if (!isset($_SESSION)) session_start();
require_once('./database/db.php');

// Create mysqli connection
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
    die("Cannot access db: " . mysqli_connect_error());
}

// Fetch products and categories
$products = [];
$sql = "SELECT tbl_product.*, tbl_category.cat_name
        FROM tbl_product
        INNER JOIN tbl_category ON tbl_product.cat_id = tbl_category.cat_id";

if ($res = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_object($res)) {
        $products[] = $row;
    }
    mysqli_free_result($res);
} else {
    die("Database query failed: " . mysqli_error($link));
}

// Helper function to validate image type
function valid($ptype)
{
    $valid_types = ["image/jpg", "image/jpeg", "image/png", "image/gif"];
    return in_array($ptype, $valid_types);
}

// Handle new product POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $proname = trim($_POST['proname']);
    $prodesc = htmlspecialchars(trim($_POST['prodesc']));
    $category = intval($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $proimage = $_FILES["proimage"];

    $errmsg_arr = [];
    $errflag = false;

    if ($proname === '') {
        $errmsg_arr[] = 'Product name missing';
        $errflag = true;
    }
    if ($category === 0) {
        $errmsg_arr[] = 'Category missing or invalid';
        $errflag = true;
    }
    if ($price <= 0) {
        $errmsg_arr[] = 'Price missing or invalid';
        $errflag = true;
    }
    if ($quantity < 0) {
        $errmsg_arr[] = 'Quantity missing or invalid';
        $errflag = true;
    }
    if (empty($proimage["tmp_name"])) {
        $errmsg_arr[] = 'Please upload an image';
        $errflag = true;
    }

    if ($errflag) {
        $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
        session_write_close();
        header("Location: index.php");
        exit();
    }

    if (!valid($proimage['type'])) {
        $_SESSION['ERRMSG_ARR'] = ['You must upload a JPEG, JPG, PNG, or GIF.'];
        header("Location: index.php");
        exit();
    }

    // Prepare upload path
    $uploadDir = __DIR__ . '/../img/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $uploadFile = $uploadDir . basename($proimage['name']);

    if (move_uploaded_file($proimage['tmp_name'], $uploadFile)) {
        // Prepare and execute insert query using prepared statements
        $stmt = mysqli_prepare($link, "INSERT INTO tbl_product (cat_id, pd_name, pd_description, pd_price, pd_qty, pd_image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issdis", $category, $proname, $prodesc, $price, $quantity, $proimage['name']);
            $execResult = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($execResult) {
                $_SESSION['MSGS'] = ['<strong>Wola!</strong> Changes were successful.'];
                session_write_close();
                header("Location: index.php");
                exit();
            } else {
                die("Insert query failed: " . mysqli_error($link));
            }
        } else {
            die("Failed to prepare statement: " . mysqli_error($link));
        }
    } else {
        $_SESSION['ERRMSG_ARR'] = ['Could not upload file. Check read/write permissions on the directory'];
        header("Location: index.php");
        exit();
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $pd_id = intval($_GET['delete']);

    // Prepare delete query
    $stmt = mysqli_prepare($link, "DELETE FROM tbl_product WHERE pd_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $pd_id);
        $execResult = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($execResult) {
            $_SESSION['MSGS'] = ['<strong>Wola!</strong> Changes were successful.'];
            session_write_close();
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['ERRMSG_ARR'] = ['<strong>Oh no!</strong> Changes didn\'t happen, make sure your database is up.'];
            session_write_close();
            header("Location: index.php");
            exit();
        }
    } else {
        die("Failed to prepare delete statement: " . mysqli_error($link));
    }
}

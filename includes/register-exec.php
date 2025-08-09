<?php
// Start session
session_start();

// Include database connection details
require_once('../database/db.php');
require '../database/central_function.php';


// Array to store validation errors
$errmsg_arr = [];
$errflag = false;

// Connect to MySQL using MySQLi
// $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
// if (!$link) {
//     die('Failed to connect to server: ' . mysqli_connect_error());
// }

// Function to sanitize values
function clean($str, $conn)
{
    return mysqli_real_escape_string($conn, trim($str));
}

// Sanitize POST values
$username   = clean($_POST['username'] ?? '', $conn);
$email      = clean($_POST['email'] ?? '', $conn);
$password   = $_POST['password'] ?? '';
$cpassword  = $_POST['cpassword'] ?? '';
$address = $_POST['address'] ?? '';
$phone = $_POST['phone'] ?? '';




// Input validations
if ($username === '') {
    $errmsg_arr[] = 'Username missing';
    $errflag = true;
}
if ($email === '') {
    $errmsg_arr[] = 'Email missing';
    $errflag = true;
}
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
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errmsg_arr[] = 'Enter a valid email ID';
    $errflag = true;
}



// var_dump($username);
// die;

// Check for duplicate username
// if ($username !== '') {
//     // $stmt = mysqli_prepare($link, "SELECT user_id FROM `user` WHERE user_name = ?");
//     $stmt = select_data('user', $conn, '*', 'where user_name = ?');
//     $result = $stmt->fetch_assoc();
//     // mysqli_stmt_bind_param($stmt, 's', $username);
//     // mysqli_stmt_execute($stmt);
//     // mysqli_stmt_store_result($stmt);
//     if ($result > 0) {
//         $errmsg_arr[] = 'Username already in use';
//         $errflag = true;
//     }
//     // mysqli_stmt_close($stmt);
// }



// Redirect on validation error
// if ($errflag) {
//     $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
//     session_write_close();
//     header("Location: ../register.php");
//     exit();
// }

// Determine if user is admin
$is_admin = preg_match("/(.*)admin/i", $username) ? 1 : 0;

// Securely hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// var_dump('hello');
// die;

$data = [
    'user_name' => $username,
    'email' => $email,
    'password' => $password,
    'address' => $address,
    'phone' => $phone
];

$user_sql = insertData('user', $conn, $data);
var_dump($user_sql);
// exit;

if ($user_sql) {
    $url =  '../login.php?success=Created Success';
    header("Location: $url");
    exit;
} else {
    var_dump("hello");
    $url = '../register.php?error=Error In Insertion';
    header("Location: $url");
    exit;
}

// Insert user into database
// $stmt = mysqli_prepare($link, "INSERT INTO tbl_user (user_name, password, user_email, created_at, updated_at, user_is_admin) VALUES (?, ?, ?, NOW(), NOW(), ?)");
// mysqli_stmt_bind_param($stmt, 'sssi', $username, $hashed_password, $email, $is_admin);

// if (mysqli_stmt_execute($stmt)) {
//     $_SESSION['MSGS'] = ['<b>Whoa you are awesome!</b> Registration Successful!'];
//     session_write_close();
//     header("Location: ../index.php");
//     exit();
// } else {
//     die("Query failed: " . mysqli_error($link));
// }

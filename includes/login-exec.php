<?php
// Start session
session_start();

// Include database connection details
require_once('../database/db.php');
require '../database/central_function.php';

// Array to store validation errors
$errmsg_arr = [];

// Validation error flag
$errflag = false;

// Connect to MySQL server using mysqli
// $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
// if (!$link) {
//     die('Failed to connect to server: ' . mysqli_connect_error());
// }

// Function to sanitize values received from the form. Prevents SQL injection
function clean($str, $conn)
{
    return mysqli_real_escape_string($conn, trim($str));
}

// Sanitize the POST values
$username = clean($_POST['username'] ?? '', $conn);
$password = $_POST['password'] ?? '';

// Input Validations
if ($username === '') {
    $errmsg_arr[] = 'Please provide a username.';
    $errflag = true;
}
if ($password === '') {
    $errmsg_arr[] = 'Please enter the password.';
    $errflag = true;
}

// If there are input validations, redirect back to the login form
if ($errflag) {
    $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
    session_write_close();
    header("Location: ../login.php");
    exit();
}

// Prepare statement to get user by username
$stmt = mysqli_prepare($conn, "SELECT user_id, user_name, password, role FROM user WHERE user_name = ?");
if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($link));
}

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 1) {
    // Bind result variables
    mysqli_stmt_bind_result($stmt, $user_id, $user_name, $hashed_password, $user_is_admin);
    mysqli_stmt_fetch($stmt);

    // Verify password
    // Assuming passwords are stored using password_hash(). If not, you should update your DB accordingly.
    if (password_verify($password, $hashed_password)) {
        // Login Successful
        session_regenerate_id(true);
        $_SESSION['SESS_USER_ID'] = $user_id;
        $_SESSION['SESS_USERNAME'] = $user_name;
        $_SESSION['SESS_IS_ADMIN'] = $user_is_admin;
        $_SESSION['user_id'] = $user_id;
        session_regenerate_id(true);

        session_write_close();
        header("Location: ../index.php");
        exit();
    } else {
        // Password incorrect
        $_SESSION['ERRMSG_ARR'] = ['<b>Oh no!</b> Incorrect username or password. Please try again.'];
        session_write_close();
        header("Location: ../login.php");
        exit();
    }
} else {
    // Username not found
    $_SESSION['ERRMSG_ARR'] = ['<b>Oh no!</b> Incorrect username or password. Please try again.'];
    session_write_close();
    header("Location: ../login.php");
    exit();
}

mysqli_stmt_close($stmt);
mysqli_close($link);

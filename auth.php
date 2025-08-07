<?php
// Start the session
session_start();

// Check if the user is logged in
if (empty($_SESSION['SESS_USER_ID'])) {
    // Redirect to access denied page if not logged in
    header("Location: access-denied.php");
    exit();
}
?>

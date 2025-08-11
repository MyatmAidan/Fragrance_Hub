<?php
// Start session at the very top
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/header.php';
include 'includes/nav.php';


//$hashed_password = password_hash("1234567", PASSWORD_DEFAULT);
//echo $hashed_password;

?>

<div id="main">
  <div class="container">
      <form method="POST" action="includes/login-exec.php" accept-charset="UTF-8" class="form-signin">
        <h3 class="form-signin-heading">Please sign in</h3>
        <?php
          if (isset($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR']) && count($_SESSION['ERRMSG_ARR']) > 0) {
        ?>
          <div class="alert alert-warning">
            <ul class="list-unstyled">
              <?php 
              foreach ($_SESSION['ERRMSG_ARR'] as $msg) {
                  echo '<li>' . htmlspecialchars($msg) . '</li>';
              }
              ?>
            </ul>
          </div>
        <?php
          unset($_SESSION['ERRMSG_ARR']); // Unset after displaying
          }
        ?>
        <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
        <hr>
        <p>Haven't registered? Please go <a href="register.php" class="btn btn-info btn-xs">Register</a></p>
      </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>

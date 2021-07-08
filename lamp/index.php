<?php
    $page_title = "Home";
    $auth_needed = false;
?>
<?php require_once("lib/page-setup.php") ?>
<?php include('common/header.php'); ?>
Home page!
<br />
<a class="btn btn-primary" href="signin.php">Log In</a>
<br />
<a class="btn btn-primary" href="signup.php">Sign Up</a>
<?php include('common/footer.php'); ?>
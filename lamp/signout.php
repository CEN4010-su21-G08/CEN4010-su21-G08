<?php
    $page_title = "Signed out";
    $auth_needed = false;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php
    User::sign_out();
?>
    <?php include('common/header.php'); ?>
    <h2>You have been signed out</h2>
<?php include('common/footer.php'); ?>
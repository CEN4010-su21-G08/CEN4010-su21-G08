<?php
    $page_title = "Signed out";
    $auth_needed = false;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php
    User::sign_out();
?>
    <?php include('common/header.php'); ?>
    <div class="signoutdiv">
        <h2 class="maintitleheader">Sign out<h2>
        <hr/>
        <h1>You have been signed out</h2>
        <br>
        <br>
        <a href="index.php">Click here to return to home page</a>
    </div>
<?php include('common/footer.php'); ?>

<?php
$page_title = "Home";
$auth_needed = false;
$center_page = true;
?>
<?php require_once("lib/page-setup.php") ?>
<?php include('common/header.php'); ?>
<h2 class="homepageheader">Welcome to Burrow!</h2>
<hr/>
<h2 class="maintitleheader">How to get started:</h2>
<br/>
<p>To get started with Burrow, you need to create an account with the button below if you do not have one already.</p>
<p>After you have created your account, join the classes that you are enrolled in this semester on the course search page.</p>
<p>Once you have joined all your courses, you are ready to start chatting!</p>
<hr/>
<br/>
<h2 class="maintitleheader">If you already have an account:</h2>
<a href="signin.php" class="btn btn-secondary">Sign In</a>
<br/>
<br/>
<h2 class="maintitleheader">If you need to create an account:</h2>
<a href="signup.php" class="btn btn-secondary">Sign Up</a>
<?php include('common/footer.php'); ?>
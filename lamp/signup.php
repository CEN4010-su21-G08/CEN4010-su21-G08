<?php
    $page_title = "Sign in";
    $auth_needed = false;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        create_user_account();
    } else {
?>
    <?php include('common/header.php'); ?>
    <a class="btn btn-primary" href="signin.php">Sign in</a>
    <form method="post" action="<?php echo(htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
    
        <input name="first_name" placeholder="first name" /><br />
        <input name="last_name" placeholder="last_name" /><br />
        <input name="email" placeholder="email" /><br />
        <input name="password" placeholder="password" /><br />
        <select name="display_name">
            <option value="1">First Last</option>
            <option value="2">First Last</option>
        </select><br />
        <button type="submit">Submit</button>
    </form>
<?php } ?>
<?php include('common/footer.php'); ?>
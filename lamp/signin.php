<?php
    $page_title = "Sign in";
    $auth_needed = false;
?>
<?php require_once("lib/page-setup.php") ?>
<?php 
function renderSignInPage($error = "") { 
    include('common/header.php');
    ?>
    
    <a class="btn btn-outline-primary" href="signup.php">Sign up</a>
    <?php if (isset($_GET['r'])) { ?>
        <div class="alert alert-warning" style="margin: 5px 15px;">
            You need to be signed in to access that page.
         </div>
    <?php } ?>
    <form method="post" action="<?php echo(htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
        <span style="color: #cc0000;"><?php echo $error; ?>
        <br />
        <input name="email" placeholder="email" <?php if ($error != '') {?>value="<?php echo htmlspecialchars($_POST['email']); ?>"<?php } ?> /><br />
        <input name="password" type="password" placeholder="password" /><br />
        <button type="submit">Submit</button>
    </form>
<?php }
?>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $em = parse_input("email");
        $p = parse_input("password");
        if ($em == NULL || $p == NULL) {
            renderSignInPage('Invalid email address or password');
        } else {
            $s = sign_in_user($em, $p);
            if ($s == 0) {
                renderSignInPage('Invalid email address or password');
            } else {
                header("Location: courses.php");
            }
        }
    } else {
?>
    <?php //include('common/header.php'); ?>
    <?php renderSignInPage() ?>
<?php } ?>
<?php include('common/footer.php'); ?>
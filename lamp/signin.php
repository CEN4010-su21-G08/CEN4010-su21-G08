<?php
    $page_title = "Sign in";
    $auth_needed = false;
?>
<?php require_once("lib/page-setup.php") ?>
<?php 
function renderSignInPage($error = "") { 
    include('common/header.php');
    ?>
    <div class="signinupdiv">
        <h2 class="maintitleheader">Sign In</h2>
        <hr/>
        <h1>Please sign in below</h1>
    <a class="btn btn-outline-primary" href="signup.php">Sign up</a>
    <?php if (isset($_GET['r'])) { ?>
        <div class="alert alert-warning" style="margin: 5px 15px;">
            You need to be signed in to access that page.
         </div>
    <?php } ?>
    <form method="post" action="<?php echo(htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
        <span style="color: #cc0000;"><?php echo $error; ?></span>
        <br />
        <div class="form-group">
            <label for="email">Email</label>
            <input name="email" placeholder="Enter Your Email Here" id="email" <?php if ($error != '') {?>value="<?php echo htmlspecialchars($_POST['email']); ?>"<?php } ?> />
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input name="password" type="password" placeholder="Enter Your Password Here" id="password"/>
        </div>
        <button type="submit" class="btn btn-secondary">Sign In</button>
    </form>
    </div>
<?php }
?>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $em = parse_input("email");
        $p = parse_input("password");
        if ($em == NULL || $p == NULL) {
            renderSignInPage('Invalid email address or password');
        } else {
            $s = User::sign_in($em, $p);
            if ($s->uid == null) {
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
<?php
    $page_title = "Sign in";
    $auth_needed = false;
    $center_page = true;
?>
<?php require_once("lib/page-setup.php") ?>
<?php
function renderSignInPage($error = "")
{
    include('common/header.php'); ?>
    <div class="signinupdiv">
        <h2 class="maintitleheader">Sign In</h2>
        <hr/>
        <p>Please sign in below</p>
    <p>
        Don't have an account? <a class="link-no-history link-no-underline" href="signup.php">Sign up here</a>
    </p>
    <?php if (isset($_GET['r'])) { ?>
        <div class="alert alert-warning" style="margin: 5px 15px;">
            You need to be signed in to access that page.
         </div>
    <?php } ?>
    <form class="burrow-form sign-in-form" method="post" action="<?php echo(htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
        <span style="color: #cc0000;"><?php echo $error; ?></span>
        <br />
        <div class="form-group">
            <label for="email">Email</label>
            <input class="form-control" name="email" placeholder="example@fau.edu" id="email" <?php if ($error != '') {?>value="<?php echo htmlspecialchars($_POST['email']); ?>"<?php } ?> />
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input class="form-control" name="password" type="password" placeholder="Password" id="password"/>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Sign In</button>
        </div>
    </form>
    </div>
<?php
}
?>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $em = parse_input("email");
        $p = parse_input("password");
        if ($em == null || $p == null) {
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
    <?php //include('common/header.php');?>
    <?php renderSignInPage() ?>
<?php
    } ?>
<?php include('common/footer.php'); ?>

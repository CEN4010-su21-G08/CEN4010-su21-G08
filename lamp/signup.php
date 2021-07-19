<?php
    $page_title = "Sign in";
    $auth_needed = false;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php 
    function render_signup_page($error=null) {
        include('common/header.php');
        ?>
        <div class="signinupdiv">
            <h2 class="maintitleheader">Sign Up</h1>
            <hr/>
        <br />
        <h1>Please create an account below</h1>
        <a class="btn btn-primary" href="signin.php">Sign in</a>
        <br />
        <?php if ($error) { ?>
        <br />
        <div class="alert alert-danger" style="margin-right: 15px;"><?php echo $error; ?></div>
        <?php } ?>
        <br />
            <form method="post" action="<?php echo(htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input required id="firstname" name="first_name" class="form-control" placeholder="Enter Your First Name Here" type="text"<?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['first_name'])); ?>" <?php } ?>placeholder="first name" />
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input required id="lastname" name="last_name" placeholder="Enter Your Last Name Here" class="form-control" type="text"<?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['last_name'])); ?>" <?php } ?> />
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input required id="email" type="email" name="email" placeholder="Enter Your Email Here" class="form-control"<?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['email'])); ?>" <?php } ?> />
                <small id="emailHelp" class="form-text text-muted">Please use your FAU email to sign up</small>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input required id="password" name="password" minlength="8" type="password" placeholder="Enter Your Password Here" class="form-control" />
                <small id="passwordHelp" class="form-text text-muted">Your password must be at least 8 characters with at least 1 number</small>
            </div>
            <div class="form-group">
                <label for="verifypass">Verify Password</label>
                <input required id="verifypass" name="verify_password" minlength="8" type="password" placeholder="Confirm Your Password Here" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="namesel">Display Name</label>
                <select required id="namesel" name="display_name" class="form-control" placeholder="Select Your Display Name Style"<?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['display_name'])); ?>" <?php } ?>>
                <option value="1">First Last</option>
                <option value="2">Last First</option>
                </select><br /><br />
            </div>
                <button type="submit" class="btn btn-secondary">Sign Up</button>
            </form>
        </div>
<?php } ?>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!validate_input($_POST, "first_name")) return render_signup_page("Please provide a first name");
        if (!validate_input($_POST, "last_name")) return render_signup_page("Please provide a last name");
        if (!validate_input($_POST, "email")) return render_signup_page("Please provide a email address");
        if (!validate_input($_POST, "password")) return render_signup_page("Please provide a password");
        if (!validate_input($_POST, "verify_password")) return render_signup_page("Please provide a password");
        if (!validate_input($_POST, "display_name")) return render_signup_page("Please provide a valid display name");

        $first_name = parse_input('first_name', true);
        $last_name = parse_input('last_name', true);
        $email = parse_input('email', true);
        $password = parse_input('password', true);
        $verify_password = parse_input('verify_password', true);
        $display_name = intval(parse_input('display_name', true));
        $error_message = User::create_user($first_name, $last_name, $email, $password, $verify_password, $display_name);
        if ($error_message != null) {
            if (empty($error_message)) {
                render_signup_page("Something went wrong");
            } else {
                render_signup_page($error_message);
            }
        }
        // create_user_account();
    } else {
?>
    <?php render_signup_page(); ?>
    
<?php } ?>
<?php include('common/footer.php'); ?>
<?php
    $page_title = "Sign in";
    $auth_needed = false;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php 
    function render_signup_page($error=null) {
        include('common/header.php');
        ?>
        <br />
        <a class="btn btn-primary" href="signin.php">Sign in</a>
        <br />
        <?php if ($error) { ?>
        <br />
        <div class="alert alert-danger" style="margin-right: 15px;"><?php echo $error; ?></div>
        <?php } ?>
        <br />
        <form method="post" action="<?php echo(htmlspecialchars($_SERVER['PHP_SELF'])); ?>">

        <input required name="first_name" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['first_name'])); ?>" <?php } ?>placeholder="first name" /><br />
        <input required name="last_name" placeholder="last_name" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['last_name'])); ?>" <?php } ?> /><br />
        <input required type="email" name="email" placeholder="email" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['email'])); ?>" <?php } ?> /><br />
        <input required name="password" minlength="8" type="password" placeholder="password" /><br />
        <input required name="verify_password" minlength="8" type="password" placeholder="Confirm Password" /><br />
        <select required name="display_name" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['display_name'])); ?>" <?php } ?>>
        <option value="1">First Last</option>
        <option value="2">Last First</option>
        </select><br /><br />
        <button type="submit">Submit</button>
        </form>
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
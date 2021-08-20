<?php
$page_title = "Sign in";
$auth_needed = false;
$center_page = true;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php
function render_signup_page($error = null)
{
    include('common/header.php');
?>
    <div class="signinupdiv">
        <h2 class="maintitleheader">Sign Up</h1>
            <hr />
            <p>Please create an account below</p>
            <p>
                Already have an account? <a class="link-no-history link-no-underline" href="signin.php">Sign in here</a>
            </p>
            <br />
            <?php if ($error) { ?>
                <div class="alert alert-danger" style="margin-right: 15px;"><?php echo $error; ?></div>
            <?php } ?>
            <br />
            <form class="burrow-form sign-up-form" method="post" action="<?php echo (htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
                <div class="form-group">
                    <label class="form-label" for="firstname">First Name</label>
                    <input required id="firstname" name="first_name" class="form-control" placeholder="First Name" type="text" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['first_name'])); ?>" <?php } ?>placeholder="first name" />
                </div>
                <div class="form-group">
                    <label class="form-label" for="lastname">Last Name</label>
                    <input required id="lastname" name="last_name" placeholder="Last Name" class="form-control" type="text" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['last_name'])); ?>" <?php } ?> />
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input required id="email" type="email" name="email" placeholder="example@fau.edu" class="form-control" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['email'])); ?>" <?php } ?> />
                    <small id="emailHelp" class="form-text text-muted">Please use your FAU email to sign up</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input required id="password" name="password" minlength="8" type="password" placeholder="Password" class="form-control" />
                    <small id="passwordHelp" class="form-text text-muted">Your password must be at least 8 characters with at least 1 number</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="verifypass">Verify Password</label>
                    <input required id="verifypass" name="verify_password" minlength="8" type="password" placeholder="Confirm Password" class="form-control" />
                </div>
                <div class="form-group">
                    <label class="form-label" for="namesel">Display Name</label>
                    <select required id="namesel" name="display_name" class="form-select">

                        <?php $selected_dn = "0";
                        if ($error) {
                            $selected_dn = $_POST['display_name'] ? htmlspecialchars($_POST['display_name']) : '0';
                        }
                        ?>
                        <option selected>Please choose a display name option</option>
                        <?php foreach (get_display_name_options() as $display_name_option) {
                            $display_name_option[0] = strval($display_name_option[0]); ?>
                            <option <?= $selected_dn == $display_name_option[0] ? "selected " : ""; ?>value="<?= $display_name_option[0]; ?>"><?= $display_name_option[1]; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Sign Up</button>
                </div>
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
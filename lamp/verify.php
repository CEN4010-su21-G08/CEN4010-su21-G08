<?php
$page_title = "Verify Account";
$verify_page = true;
$center_page = true;
?>
<?php require_once("lib/page-setup.php") ?>
<?php include('common/header.php'); ?>
<?php
global $user;
function showVerificationHelp()
{
    global $user;
    if (!isset($_GET['code'])) {
?>
        <div class="main-content-center" style="text-align: left;">
            <h2 class="maintitleheader" style="text-align: center;">Verify Email Address</h2>
            <hr />
            <br />
            <h3>Welcome, <?= htmlspecialchars(get_display_name($user->first_name, $user->last_name, $user->display_name)); ?></h3>
            <p>In order to use this site, you'll need to confirm you own the email address you provided.</p>
            <p>If you do not own the email address <strong><?= htmlspecialchars(filter_var($user->email, FILTER_SANITIZE_EMAIL)); ?></strong>, please sign out and create a new account with an email address you do own.</p>
            <p>After signing in, Burrow will confirm your email address is attached to your Google account, and then will store only your Google User ID.</p>
            <br />
        </div>
    <?php
    }
}
if (isset($_GET['success'])) {
    if ($user->has_verified()) {
    ?>
        <div class='alert alert-success d-flex align-items-center' role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Success:">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
            </svg>
            <div>Congrats, you've successfully verified your email address. Now that you've taken care of that, you can <a href="courses.php" class="alert-link">select your courses</a>.</div>
        </div>
        <br />

    <?php showVerificationHelp();
    } else { ?>
        <div class='alert alert-danger d-flex align-items-center' role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Error:">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </svg>
            Something went wrong. Please try again.
        </div>
        <?php showVerificationHelp(); ?>
        <?php $user->create_google_sign_in_button(); ?>
    <?php
    }
} else {
    if ($user->has_verified()) {
        echo "You're already verified.";
    ?>
        <script>
            window.location.href = 'courses.php';
        </script>
<?php
    } else {
        if (isset($_GET['code']) && !empty($_GET['code'])) {
            $user->handle_google_callback($_GET['code']);
        } else {
            showVerificationHelp();
            $user->create_google_sign_in_button();
        }
    }
}


?>
<?php include('common/footer.php'); ?>
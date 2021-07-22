<?php
    $page_title = "Verify Account";
    $verify_page = true;
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
                <h2>Account verification</h2>
                <p>In order to use this site, you'll need to confirm you own the email address you provided.</p>
                <p>If you do not own the email address <code><?php echo(filter_var($user->email, FILTER_SANITIZE_EMAIL)); ?></code>, please sign out and create a new account with an email address you do own.</p>
                <p>After signing in, Burrow will confirm your email address is attached to your Google account, and then will store only your Google User ID.</p>
            <?php
        }
    }
    if (isset($_GET['success'])) {
        if ($user->has_verified()) {
            echo "<div class='alert alert-success'>Congrats, you've been verified!</div>";
            showVerificationHelp();
        } else { ?>
            <div class='alert alert-danger'>Something went wrong.</div>
            <p>Please try again below</p>
            <?php showVerificationHelp(); ?>

            <?php $user->create_google_sign_in_button();
        }
    } else {
        if ($user->has_verified()) {
            echo "You're already verified."; ?> 
            <script>window.location.href = 'courses.php';</script>
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
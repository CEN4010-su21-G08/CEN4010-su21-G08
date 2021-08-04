<?php
$page_title = "User Settings";
$center_page = true;
?>
<?php require_once("lib/page-setup.php") ?>
<?php
/**
 * POST
 *      ?action=
 *          change_display_name: update the display name
 *          change_password: change password (old, new, new confirm)
 *          change_name: change first and/or last name
 * DELETE
 *      delete user's account
 * GET
 *      open the account/profile page
 */
?>
<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        global $user;
        if ($action == 'change_display_name') {
            // change display name
            if (!isset($_POST['display_name'])) {
                echo ("Missing display name");
                die();
            }
            $new_display_name = $_POST['display_name'];
            $user->change_display_name($new_display_name);
            header("Location: account.php?changed=display_name");
        } else if ($action == 'change_password') {
            // change password
        } else if ($action == 'change_name') {
            // change name
        } else {
            echo 'Invalid action';
            die();
        }
    }
    echo "Invalid action";
    die();
    //
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    // delete account
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    // show page
    include('common/header.php');
?>
    <?php
    if (isset($_GET['changed'])) {
        $_changed = $_GET['changed'];
        $changed = null;

        if ($_changed == 'display_name') {
            $changed = "display name";
        } else if ($_changed == 'password') {
            $changed = "password";
        } else if ($_changed == 'name') {
            $changed = "name";
        }

        if ($changed !== null) { ?>
            <div class="alert alert-success">
                Successfully changed your <?= $changed; ?>
            </div>
    <?php }
    }
    ?>
    <h2 class="maintitleheader">Account Settings</h2>
    <form method="post" action="account.php?action=change_display_name">
        <label>Display name</label>
        <input type="number" name="display_name" />
    </form>
    <hr />

    <script>
        let search = new URLSearchParams(window.location.search);
        if (search.get("changed") || search.get("action")) {
            window.history.replaceState({}, '', "account.php");
        }
    </script>
<?php
    include('common/footer.php');
} else {
    echo "Unsupported method";
    die();
}

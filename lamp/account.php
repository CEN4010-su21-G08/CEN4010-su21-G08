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
            $result = $user->change_display_name($_POST['display_name']);
            if ($result != null) {
                echo $result;
                die();
            }
            header("Location: account.php?changed=display_name");
        } else if ($action == 'change_password') {
            // change password
            if (!isset($_POST['current_password'])) {
                //invalid
                die();
            }
            if (!isset($_POST['new_password'])) {
                //invalid
                die();
            }
            if (!isset($_POST['confirm_new_password'])) {
                //invalid
                die();
            }
            $result = $user->change_password($_POST['current_password'], $_POST['new_password'], $_POST['confirm_new_password']);
            if ($result != null) {
                echo $result;
                die();
            }
            header("Location: account.php?changed=password");
        } else if ($action == 'change_name') {
            // change name
            if (!isset($_POST['first_name'])) {
                echo "Missing first name";
                die();
            }
            if (!isset($_POST['last_name'])) {
                echo "Missing last name";
                die();
            }
            $result1 = $user->change_first($_POST['first_name']);
            $result2 = $user->change_last($_POST['last_name']);

            if ($result1 != null) {
                echo $result1;
                die();
            }
            if ($result2 != null) {
                echo $result2;
                die();
            }
            header("Location: account.php?changed=name");
        } else if ($action = 'deactivate_account') {
            $user->deactivate_account();
            header("Location: account.php?changed=deactivated");
        }  else if ($action = 'delete_account') {
            $user->delete_account();
            header("Location: account.php?changed=deleted");
        }   else {
            echo 'Invalid action';
            die();
        }
    }
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
    <form method="post" action="account.php?action=change_password">
        <label>Current Password</label>
        <input type="password" name="current_password" />
        <label>New Password</label>
        <input type="password" name="new_password" />
        <label>Confirm New Password</label>
        <input type="password" name="confirm_new_password" />
        <button type="submit">Submit</button>
    </form>
    <hr />
    <form method="post" action="account.php?action=change_name">
        <label>First Name</label>
        <input type="text" name="first_name" />
        <label>Last Name</label>
        <input type="text" name="last_name" />
        <button type="submit">Submit</button>
    </form>
    <hr />
    <form method="post" action="account.php?action=deactivate_account">
        <button type="submit">Disable</button>
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

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
<?php function showAccountPage($error = null)
{ ?>
    <?php include('common/header.php'); ?>
    <div class="account_page">
        <h2 class="maintitleheader">Account Settings</h2>
        <hr />
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
        if ($error != null) { ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php } ?>
        <h3 class="h5">Change Password</h3>
        <form style="text-align: left;" class="g-3 bur-account-settings-pass" method="post" action="account.php?action=change_password">
            <div class="row">
                <div class="col-md-12">
                    <label for="current_password" class="form-label">Current Password<span class="form_required"></span></label>
                    <input name="current_password" type="password" class="form-control" id="current_password" placeholder="Current Password">
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-6">
                    <label for="new_password" class="form-label">New Password<span class="form_required"></span></label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New password">
                </div>
                <div class="col-md-6">
                    <label for="confirm_new_password" class="form-label">Confirm New Password<span class="form_required"></span></label>
                    <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password">
                </div>
                <small class="form-text text-muted">Password must have 8 characters and must have at least three of the following: a lowercase letter, an uppercase letter, number, or a symbol</small>
            </div>
            <br />
            <div class="col-12" style="text-align: center;">
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
        <br />
        <hr />
        <br />
        <form style="text-align: left;" class="row g-3" method="post" action="account.php?action=change_display_name">
            <div class="col-12">
                <label for="display_name" class="form-label">Display Name<span class="form_required"></span></label>
                <?php
                global $user;
                $selected_dn = $user->display_name; ?>
                <select name="display_name" id="display_name" class="form-select">
                    <option disabled selected>Please choose a display name option</option>
                    <?php foreach (get_display_name_options() as $display_name_option) {
                        $display_name_option[0] = strval($display_name_option[0]); ?>
                        <option <?= $selected_dn == $display_name_option[0] ? "selected " : ""; ?>value="<?= $display_name_option[0]; ?>"><?= $display_name_option[1]; ?></option>
                    <?php } ?>
                </select>
                <br />
                <div class="col-12" style="text-align: center;">
                    <button type="submit" class="btn btn-primary">Change Display Name</button>
                </div>
            </div>
        </form>
        <br />
        <hr />
        <br />
        <form style="text-align: left;" class="row g-3" method="post" action="account.php?action=change_name">
            <div class="col-md-6">
                <label for="first_name" class="form-label">First Name<span class="form_required"></span></label>
                <input name="first_name" type="text" class="form-control" id="first_name" placeholder="Enter your first name here">
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label">Last Name<span class="form_required"></span></label>
                <input name="last_name" type="text" class="form-control" id="last_name" placeholder="Enter your last name here">
            </div>
            <br />
            <div class="col-12" style="text-align: center;">
                <button type="submit" class="btn btn-primary">Change Name</button>
            </div>
        </form>
        <br />
        <hr />
        <br />
        <form class="row g-3" method="post" action="account.php?action=delete_account">
            <div class="d-grid gap-4 col-6 mx-auto">
                <button type="submit" class="btn btn-outline-danger" type="submit">Delete Account</button>
            </div>
        </form>
    </div>
    </div>

    <script>
        let search = new URLSearchParams(window.location.search);
        if (search.get("changed") || search.get("action")) {
            window.history.replaceState({}, '', "account.php");
        }
    </script>
    <?php include('common/footer.php'); ?>
<?php } ?>
<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        global $user;
        if ($action == 'change_display_name') {
            // change display name
            if (!isset($_POST['display_name'])) {
                showAccountPage("Display name is required.");
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
                showAccountPage("Current password is required.");
                die();
            }
            if (!isset($_POST['new_password'])) {
                //invalid
                showAccountPage("New password is required.");
                die();
            }
            if (!isset($_POST['confirm_new_password'])) {
                //invalid
                showAccountPage("Confirmed new password is required.");
                die();
            }
            $u = new User($user->uid, null, ["uid", "email", "first_name", "last_name", "display_name", "flags", "google_user_id", "password"]);
            $result = $u->change_password($_POST['current_password'], $_POST['new_password'], $_POST['confirm_new_password']);
            if ($result != null) {
                showAccountPage($result);
                die();
            }
            header("Location: account.php?changed=password");
        } else if ($action == 'change_name') {
            // change name
            if (!isset($_POST['first_name'])) {
                showAccountPage("First name is required.");
                die();
            }
            if (!isset($_POST['last_name'])) {
                showAccountPage("Last name is required.");
                die();
            }
            $result1 = $user->change_first($_POST['first_name']);
            $result2 = $user->change_last($_POST['last_name']);

            if ($result1 != null) {
                showAccountPage($result1);
                die();
            }
            if ($result2 != null) {
                showAccountPage($result2);
                die();
            }
            header("Location: account.php?changed=name");
        } else if ($action == 'deactivate_account') {
            $user->deactivate_account();
            header("Location: account.php?changed=deactivated");
        } else if ($action == 'delete_account') {
            $user->delete_account();
            header("Location: account.php?changed=deleted");
        } else {
            echo 'Invalid action';
            die();
        }
    }
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    // show page 
    showAccountPage();
?>

<?php } else {
    echo "Unsupported method";
    die();
}

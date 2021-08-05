<?php
$page_title = "Manage Group";
?>
<?php require_once("lib/page-setup.php") ?>
<?php require_once("lib/course-handler.php") ?>
<?php require_once("lib/channel-handler.php") ?>

<?php
if (!isset($_GET['ch_id'])) {
    header("Location: courses.php");
    die();
}
$ch_id = $_GET['ch_id'];
$group = new Channel($ch_id);
if ($group->ch_id == null) {
    include('./common/header.php');
?>
    <div class="alert alert-danger" style="margin: 20px;">You don't have access to this page or it doesn't exist</div>
<?php
    include('./common/footer.php');
    die();
}
$is_instructor = is_user_instructor($group->course_id);
function map_group_members($m)
{
    return $m->uid;
}
if ($_SERVER["REQUEST_METHOD"] == "GET") { ?>
    <?php
    include('./common/header.php');
    if (!$is_instructor) { ?>
        <div class="alert alert-danger" style="margin: 20px;">You don't have access to this page or it doesn't exist</div>
    <?php } else {
        $course_id = $group->course_id;
        $course = new Course($course_id);
        $groups = Channel::get_users_channels_in_course($user->uid, $course_id, true); ?>

        <?php show_sidebar("Course", $course->course_code . "-" . $course->section_number, $course_id, null, $groups, $is_instructor); ?>
        <div class="channels_main manage-course">
            <h2 class="maintitleheader">Manage Group</h2>
            <hr />
            <form class="bur-mod-group-name" method="post" action="">
                <div class="row g-3">
                    <label class="col-4 col-form-label" for="modGroupName">Group Name:</label>
                    <div class="col-5">
                        <input type="text" class="form-control" name="mod_group_name" placeholder="New group name" id="modGroupName" />
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-secondary mb-3">Change</button>
                    </div>
                </div>
            </form>
            <hr />
            <br />
            <form class="bur-mod-group-members" method="post" action="manage-group.php?ch_id=<?= urlencode(htmlspecialchars($group->ch_id)); ?>&action=members">
                <h6>Click the following students to add to the group:</h6>
                <div class="list-group class-member-list">
                    <?php
                    $g_members = Channel::get_members($group->ch_id);

                    $g_members = array_map('map_group_members', $g_members);
                    $c_members = Channel::get_members($group->course_id);
                    if (count($c_members) <= 0) {
                    ?>
                        No students in course
                        <?php
                    } else {
                        foreach ($c_members as $member) { ?>
                            <label class="list-group-item">
                                <input <?= in_array($member->uid, $g_members) ? "checked " : "" ?>class="form-check-input me-1" type="checkbox" name="<?= htmlspecialchars($member->uid); ?>">
                                <?= htmlspecialchars($member->display_name); ?>
                            </label>
                    <?php }
                    }
                    ?>
                </div>
                <br />
                <div class="d-grid col-6 gap-2 mx-auto">
                    <button type="submit" class="btn btn-outline-secondary">Add Users</button>
                </div>
            </form>
            <br />
            <hr />
            <br />
            <div class="d-grid col-6 mx-auto">
                <a href="delete-group.php?ch_id=<?= urlencode(htmlspecialchars($group->ch_id)); ?>" class="btn btn-outline-danger">Delete Group</a>
            </div>
        </div>

<?php include('./common/footer.php');
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    if (isset($_POST['mod_group_name']))
    {
        $new_name = $_POST['mod_group_name'];
        $group->change_name($new_name);
        echo($new_name);
    }

    $users_in_course = CourseMembership::get_users_in_course($group->course_id);
    foreach ($users_in_course as $user)
    {
        if (isset($_POST[$user->uid]))
        {
            GroupMembership::create_membership($user->uid, $group->ch_id);
        }
    }

    header("Location: manage-group.php?ch_id=" . urlencode($ch_id));
 } ?>
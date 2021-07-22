<?php
$page_title = "Create Group";
$include_sidebar = true;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php include('lib/message-handler.php'); ?>
<?php include('lib/channel-handler.php'); ?>
<?php include('lib/course-handler.php');  ?>
<?php
if (!isset($_GET['course_id'])) {
    header("Location: courses.php");
    die();
}
$course_id = $_GET["course_id"];
$course = new Course($course_id);
$is_instructor = is_user_instructor($course->course_id);
if ($_SERVER["REQUEST_METHOD"] == "GET") { ?>
    <?php
    $users_in_course = CourseMembership::get_users_in_course($course->course_id);

    include('./common/header.php');
    if (!$is_instructor) { ?>
        <div class="alert alert-danger" style="margin: 20px;">You don't have access to this page or it doesn't exist</div>
    <?php } else {
        $groups = Channel::get_users_channels_in_course($_SESSION['uid'], $course_id, true); ?>
        <?php show_sidebar("Course", $course->course_code . "-" . $course->section_number, $course_id, $groups, $is_instructor); ?>
        <div class="channels_main">
        <h2>Create a Group</h2>
        <?php 
            function render_create_group_page($error=null) { ?>
                <?php
                global $course, $course_id;
                $users_in_course = CourseMembership::get_users_in_course($course->course_id);
                if ($error) { ?>
                <br />
                <div class="alert alert-danger" style="margin-right: 15px;"><?php echo $error; ?></div>
                <?php } ?>
                <br />
                <form method="post" action="create-group.php?course_id=<?= htmlspecialchars($course_id) ?>">
                <input required name="group_name" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['group_name'])); ?>" <?php } ?>placeholder="group name" /><br />
                <div id="list1" class="dropdown-check-list" tabindex="100">
                    <span class="anchor">Users</span>
                    <ul class="items">
                        <?php
                        foreach ($users_in_course as $user)
                        { ?>
                           <!-- <li><input type="checkbox" required name = "<?php echo(htmlspecialchars($user->uid)) ?>" value = 1/><?php echo($user->first_name); echo(" "); echo($user->last_name); ?> </li> -->
                        <?php } ?>
                    </ul>
                </div>
                <br />
                <br />
                <button type="submit">Submit</button>
                </form>
        <?php } ?>
    <?php } ?>
            <?php render_create_group_page(); ?>
        </div>
        <script>
            var checkList = document.getElementById('list1');
            checkList.getElementsByClassName('anchor')[0].onclick = function(evt) {
            if (checkList.classList.contains('visible'))
                checkList.classList.remove('visible');
            else
                checkList.classList.add('visible');
            }
        </script>
        </div>
        <?php include('common/footer.php'); ?>
    <?php }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {  
            if (!validate_input($_POST, "group_name")) return render_create_group_page("Please provide a group name");

            $group_name = parse_input('group_name', true);
            Channel::create_channel($course->course_id, $group_name, 2);
        }
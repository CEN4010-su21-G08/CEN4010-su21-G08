<?php
$page_title = "Manage Course";
?>
<?php
require_once("lib/page-setup.php");
require_once("lib/course-handler.php");
require_once("lib/channel-handler.php");
require_once("lib/authentication.php");
?>
<?php
if (!isset($_GET['course_id'])) {
    header("Location: courses.php");
    die();
}
$course_id = $_GET['course_id'];
$course = new Course($course_id);
$is_instructor = is_user_instructor($course_id);

if ($_SERVER["REQUEST_METHOD"] == "GET") { ?>
    <?php

    include('./common/header.php');
    if (!$is_instructor) { ?>
        <div class="alert alert-danger" style="margin: 20px;">You don't have access to this page or it doesn't exist</div>
    <?php } else {
        $users_in_course = CourseMembership::get_users_in_course($course->course_id);
        $groups = Channel::get_users_channels_in_course($user->uid, $course_id, true); ?>
        <?php show_sidebar("Course", $course->course_code . "-" . $course->section_number, $course_id, null, $groups, $is_instructor); ?>
        <div class="channels_main">
        <h2>Manage Course</h2>
    <?php } ?>
<?php } ?>
<?php
$page_title = "Course Info";
$include_sidebar = true;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php include('lib/channel-handler.php'); ?>
<?php include('lib/course-handler.php');  ?>
<?php

if ($_SERVER["REQUEST_METHOD"] == "GET") { ?>
    <?php
    if (!isset($_GET['course_id'])) {
        header("Location: courses.php");
        die();
    }
    $course_id = $_GET["course_id"];
    $course = new Course($course_id);
    if (!$course->course_id) {
        header("Location: courses.php");
        die();
    }
    $is_instructor = is_user_instructor($course->course_id);

    $has_access = does_user_have_access($_SESSION['uid'], $course->course_id);
    include('./common/header.php');
    if (!$has_access) { ?>
        <div class="alert alert-danger" style="margin: 20px;">You don't have access to this channel or it doesn't exist</div>
    <?php } else {

        $groups = Channel::get_users_channels_in_course($user->uid, $course->course_id, true);
        $members = [];

        show_sidebar("Course", $course->course_code . "-" . $course->section_number, $course->course_id, null, $groups, $is_instructor, $members);
    ?>

        <h2 class="maintitleheader main-content-center">All Courses</h2>
        <hr />
        <div style="max-width:70vw;">
            <h1><?= htmlspecialchars($course->course_code); ?>-<?= htmlspecialchars($course->section_number); ?><?php if ($course->course_name) { ?> - <?= htmlspecialchars($course->course_name); ?> <?php } ?></h1>
            <?php if ($course->instructor_name) { ?>
                <br />
                <h2><?= htmlspecialchars($course->instructor_name); ?></h2>
            <?php } ?>
            <?php if ($course->instructor_email) { ?>
                <br />
                <h5>Email: <?= htmlspecialchars($course->instructor_email); ?></h5>
            <?php } ?>
            <?php if ($course->course_description) { ?>
                <br />
                <h5>Course Description:</h5>
                <p><?= str_replace("\n", "<br />", htmlspecialchars($course->course_description)); ?></p>
            <?php } ?>
        </div>
<?php }
} ?>
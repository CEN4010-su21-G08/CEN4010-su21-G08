<?php
$page_title = "Manage Courses (ADMIN)";
$center_page = true;
?>
<?php
require_once("lib/page-setup.php");
require_once("lib/course-handler.php");
require_once("lib/authentication.php");
?>
<?php
include('./common/header.php');
if (!($user->is_admin())) { ?>
    <div class="alert alert-danger" style="margin: 20px;">You don't have access to this page or it doesn't exist</div>
<?php } else { ?>
    <h2 class="maintitleheader">All Courses</h2>
    <hr />
    <br />
    <div class="d-grid col-6 mx-auto">
        <a href="create-course.php" class="btn btn-outline-primary">Create Course</a>
    </div>

    <?php
    $courses = Course::get_all_courses();
    ?>
    <br />
    <br />
    <ul class="list-group all-course-list">
        <?php foreach ($courses as $course) { ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <a><?= htmlspecialchars($course->course_code . '-' . $course->section_number); ?></a>
                <span><a href="manage-course.php?course_id=<?= urlencode(htmlspecialchars($course->course_id)); ?>" class="btn btn-outline-dark">Manage</a></span>
            </li>
        <?php } ?>
    </ul>
<?php }

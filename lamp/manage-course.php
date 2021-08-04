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
if ($course->course_id == null) {
    include('./common/header.php'); ?>
    <div class="alert alert-danger" style="margin: 20px;">You don't have access to this page or it doesn't exist</div>
<?php
    include('./common/footer.php');
    die();
}
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
        <div class="channels_main manage-course">
            <div>
                <h2 class="maintitleheader">Manage Course</h2>
                <hr />
                <form class="bur-mod-course-code" method="post" action="manage-course.php?modify=course_code">
                    <div class="row g-3">
                        <label class="col-4 col-form-label" for="course_code">Course Code:</label>
                        <div class="col-5">
                            <input type="text" class="form-control" name="course_code" placeholder="Course code (e.g. CEN4010)" id="course_code" />
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-secondary mb-3">Change</button>
                        </div>
                    </div>
                </form>
                <br />
                <form class="bur-mod-section-number" method="post" action="manage-course.php?modify=section_number">
                    <div class="row g-3">
                        <label class="col-4 col-form-label" for="section_number">Section Number</label>
                        <div class="col-5">
                            <input type="text" class="form-control" name="section_number" placeholder="Section number (e.g. 001)" id="section_number" />
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-secondary mb-3">Change</button>
                        </div>
                    </div>
                </form>
                <br />
                <form class="bur-mod-course-name" method="post" action="manage-course.php?modify=course_name">
                    <div class="row g-3">
                        <label class="col-4 col-form-label" for="course_name">Course Name</label>
                        <div class="col-5">
                            <input type="text" class="form-control" name="course_name" placeholder="Course name (e.g. Principles of Software Engineering)" id="course_name" />
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-secondary mb-3">Change</button>
                        </div>
                    </div>
                </form>
                <br />
                <form class="bur-mod-instructor-email" method="post" action="manage-course.php?modify=instructor_email">
                    <div class="row g-3">
                        <label class="col-4 col-form-label" for="instructor_email">Instructor Email</label>
                        <div class="col-5">
                            <input type="text" class="form-control" name="instructor_email" placeholder="Instructor's email" id="instructor_email" />
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-secondary mb-3">Change</button>
                        </div>
                    </div>
                </form>
                <br />
                <form class="bur-mod-instructor-name" method="post" action="manage-course.php?modify=instructor_name">
                    <div class="row g-3">
                        <label class="col-4 col-form-label" for="instructor_name">Instructor Name</label>
                        <div class="col-5">
                            <input type="text" class="form-control" name="instructor_name" placeholder="Instructor's name" id="instructor_name" />
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-secondary mb-3">Change</button>
                        </div>
                    </div>
                </form>
                <br />
                <form class="bur-mod-course-desc" method="post" action="manage-course.php?modify=course_description">
                    <div class="row g-3">
                        <label class="col-4 col-form-label" for="course_description">Course Description</label>
                        <div class="col-5">
                            <textarea class="form-control" name="course_description" id="course_description"></textarea>
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-secondary mb-3">Change</button>
                        </div>
                    </div>
                </form>
                <br />
                <hr />
                <div class="d-grid gap-2">
                    <a class="col-6 mx-auto btn btn-danger btn-block" href="delete-course.php?course_id=<?= urlencode(htmlspecialchars($course->course_id)) ?>">Delete Course</a>
                </div>
            </div>
        </div>
    <?php include('./common/footer.php');
    } ?>
<?php } ?>
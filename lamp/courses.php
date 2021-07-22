<?php
$page_title = "My Courses";
$center_page = true;
?>
<?php require_once("lib/page-setup.php") ?>
<?php require_once("lib/course-handler.php") ?>
<?php include('common/header.php'); ?>
<?php
function course_list_show_course($course, $stu_only = null)
{
    if ($stu_only === null || ($stu_only === false && $course->is_instructor()) || ($stu_only == true && !$course->is_instructor())) { ?>
        <a href="channels.php?ch_id=<?php echo (urlencode(htmlspecialchars($course->course_id))); ?>" class="list-group-item list-group-item-action"><?php echo (htmlspecialchars($course->course_code)); ?>-<?php echo (htmlspecialchars($course->section_number)); ?></a>
<?php
    }
}
?>
<?php
$courses = CourseMembership::get_user_courses($_SESSION['uid']);
$course_types = ['instructor' => 0, 'student' => 0];
foreach ($courses as $course) {
    if ($course->is_instructor()) $course_types['instructor']++;
    else $course_types['student']++;
}
// $courses[0]->role = 0;

?>
Courses<br />
<form class="burrow-choose-course-form" method="post" action="">
    <span style="color: #cc0000;"></span>
    <div class="mb-3 mt-3">
        <label for="courseCode">Search for courses</label>
        <input type="search" class="mt-1 form-control" name="courseCode" placeholder="Start typing... ex: CEN4010-001" id="courseCode" />
    </div>
</form>
<hr />
<h2 class="h5">Your Courses</h2>
<?php if ($course_types['student'] > 0) { ?>
    <div class="course-list">
        <h3 class="h6">Joined as a student:</h3>
        <div class="list-group">
            <?php foreach ($courses as $course) {
                course_list_show_course($course, true);
            } ?>
        </div>
    </div>
<?php }
if ($course_types['instructor'] > 0) { ?>
    <div class="course-list">
        <h3 class="h6">Joined as an instructor:</h3>
        <div class="list-group">
            <?php foreach ($courses as $course) {
                course_list_show_course($course, false);
            } ?>
        </div>
    </div>
<?php } ?>
<br />
<?php include('common/footer.php'); ?>
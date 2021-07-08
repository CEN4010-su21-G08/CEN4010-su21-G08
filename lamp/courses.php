<?php
    $page_title = "Courses";
?>
<?php require_once("lib/page-setup.php") ?>
<?php require_once("lib/course-handler.php") ?>
<?php include('common/header.php'); ?>
<?php 
function course_list_show_course($course) { ?>
    <li><a href="channels.php?ch_id=<?php echo(urlencode(htmlspecialchars($course->course_id)));?>"><?php echo(htmlspecialchars($course->course_code)); ?>-<?php echo(htmlspecialchars($course->section_number)); ?></a></li>
<?php } ?>
<?php
    $courses = CourseMembership::get_user_courses($_SESSION['uid']);
    
?>
Courses<br />
<ul>
    <?php foreach($courses as $course) {course_list_show_course($course);}?>
</ul>
<br />
<?php include('common/footer.php'); ?>
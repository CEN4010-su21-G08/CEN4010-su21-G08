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
        <?php }
    else
    { ?>
        <?php
        $courses = Course::get_all_courses();
        ?>
        <a href="create-course.php">Create a Course</a>
        <?php
        foreach ($courses as $course)
        { ?>
            <br/>
            <br/>
            <h1><?= $course->course_code ?></h1>
            <?php if (isset($course->course_name)) { ?>
                <h3><?= $course->course_name ?></h3>
            <?php } ?> 
            <a href="manage-course.php?course_id=<?=urlencode($course->course_id)?>">Manage Course</a><br/>
            <a href="delete-course.php?course_id=<?=urlencode($course->course_id)?>">Delete Course</a><br/>
        <?php } ?>
    <?php }
        
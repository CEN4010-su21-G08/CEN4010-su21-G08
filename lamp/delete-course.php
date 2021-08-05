<?php
$page_title = "Delete Group";
$center_page = true;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php include('lib/channel-handler.php'); ?>
<?php include('lib/course-handler.php');  ?>

<?php
if (!isset($_GET['course_id'])) {
    header("Location: courses.php");
    die();
}
$course_id = $_GET["course_id"];
$course = new Course($course_id);
$is_instructor = is_user_instructor($course_id);
include('./common/header.php');
function renderDeleteCoursePage($error=null) { ?>
    <h1>Delete a Course</h1>  
    <?php
    global $course_id, $course;
    if ($error) { ?>
    <br />
    <div class="alert alert-danger" style="margin-right: 15px;"><?php echo $error; ?></div>
    <?php } ?>
    <br />
    <div class="alert alert-warning" style="margin-right: 15px;">Type 'confirm' into the box below to delete class <?= $course->course_code . "-" . $course->section_number?></div>
    <form method="post" action="delete-course.php?course_id=<?= htmlspecialchars($course_id) ?>">
    <input required name="confirm" placeholder="type 'confirm'" /><br />
    <br />
    <br />
    <button type="submit">Submit</button>
    </form>
<?php }
if ($_SERVER["REQUEST_METHOD"] == "GET") { ?>
    <?php
    if (!isset($_GET['course_id'])) {
        header("Location: courses.php");
        die();
    }
    if (!$is_instructor)
    { ?>
        <div class="alert alert-danger" style="margin: 20px;">You don't have access to this page or it doesn't exist</div>
    <?php }
    else
    { ?>
        <?php renderDeleteCoursePage(); ?>
    <?php } ?>
<?php }
if ($_SERVER["REQUEST_METHOD"] == "POST") {  
        if (!$is_instructor) { ?>
            <div class="alert alert-danger" style="margin: 20px;">You don't have access to this page or it doesn't exist</div>
        <?php 
        } else {
            $confirmed = parse_input("confirm");
            if (!isset($confirmed))
            {
                renderDeleteCoursePage("Please type 'confirm' into the field below");
            }
            else if ($confirmed == "confirm")
            {
                $course->delete_course();
                header("Location: courses.php");
            }
            else
            {
                renderDeleteCoursePage("Please type 'confirm' into the field below");
            }
        }
    }



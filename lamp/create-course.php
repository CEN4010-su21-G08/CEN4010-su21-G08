<?php
$page_title = "Create Course";
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
    <h2 class="maintitleheader">Create Course</h2>
    <hr />
    <?php
    function renderCreateCoursePage($error = null)
    { ?>
        <?php
        if ($error) { ?>
            <br />
            <div class="alert alert-danger" style="margin-right: 15px;"><?php echo $error; ?></div>
        <?php } ?>
        <br />
        <form style="text-align: left;" class="burrow-create-course-form" method="post" action="create-course.php">
            <div class="form-group">
                <label class="form-label" for="course_code">Course code<span aria-label="required" class="form_required"></span></label>
                <input required <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['course_code'])); ?>" <?php } ?> type="text" class="form-control" name="course_code" placeholder="e.g. CEN4010" id="course_code" />
            </div>
            <br />
            <div class="form-group">
                <label class="form-label" for="section_number">Section number<span aria-label="required" class="form_required"></span></label>
                <input required <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['section_number'])); ?>" <?php } ?> type="text" class="form-control" name="section_number" placeholder="Section number (e.g. 001)" id="section_number" />
            </div>
            <br />
            <div class="form-group">
                <label class="form-label" for="instructor_email">Instructor email<span aria-label="required" class="form_required"></span></label>
                <input required <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['instructor_email'])); ?>" <?php } ?> type="email" class="form-control" name="instructor_email" placeholder="Instructor's Email" id="instructor_email" />
            </div>
            <br />
            <div class="form-group">
                <label class="form-label" for="course_name">Course name</label>
                <input <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['course_name'])); ?>" <?php } ?> type="text" class="form-control" name="course_name" placeholder="e.g. Principles of Software Engineering" id="course_name" />
            </div>
            <br />
            <div class="form-group">
                <label class="form-label" for="instructor_name">Instructor name</label>
                <input <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['instructor_name'])); ?>" <?php } ?> type="text" class="form-control" name="instructor_name" placeholder="Instructor's name" id="instructor_name" />
            </div>
            <br />
            <div class="form-group">
                <label class="form-label" for="course_description">Course Description:</label>
                <textarea <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['course_description'])); ?>" <?php } ?> class="form-control" name="course_description" id="newCourseDesc"></textarea>
                <small id="course_description" class="form-text text-muted">Enter a brief description of the course</small>
            </div>
            <br />
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Course</button>
            </div>
        </form>
        <br />
        <a href="manage-all-courses.php">Back to Manage Courses</a>
    <?php } ?>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $course_code = parse_input("course_code");
        $section_number = parse_input("section_number");
        $instructor_email = parse_input("instructor_email");
        $instructor_name = parse_input("instructor_name");
        $course_description = parse_input("course_description");
        $course_name = parse_input("course_name");

        if ($course_code == null)
            renderCreateCoursePage("Please enter a valid course code");
        else if ($section_number == null)
            renderCreateCoursePage("Please enter a valid section number");
        else if ($instructor_email == null)
            renderCreateCoursePage("Please enter a valid instructor email");
        else {
            $new_course = Course::create_course($course_code, $section_number, $instructor_email, $instructor_name, $course_description, $course_name);
            if ($new_course->course_id == null) {
                renderCreateCoursePage("Something went wrong, please try again");
            } else {
                renderCreateCoursePage();
            }
        }
    } else {
    ?>
        <?php renderCreateCoursePage() ?>
    <?php } ?>
    <?php include('common/footer.php'); ?>
<?php }

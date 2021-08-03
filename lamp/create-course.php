<?php
    $page_title = "Create Course (ADMIN)";
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
        <h1>Create a Course</h2>
        <?php
        function renderCreateCoursePage($error=null) { ?>
                <?php
                if ($error) { ?>
                <br />
                <div class="alert alert-danger" style="margin-right: 15px;"><?php echo $error; ?></div>
                <?php } ?>
                <br />
                <form method="post" action="create-course.php">
                <input required name="course_code" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['course_code'])); ?>" <?php } ?>placeholder="Course Code" /><br />
                <input required name="section_number" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['section_number'])); ?>" <?php } ?>placeholder="Section Number" /><br />
                <input required name="instructor_email" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['instructor_email'])); ?>" <?php } ?>placeholder="Instructor Email" /><br />
                <input name="instructor_name" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['instructor_name'])); ?>" <?php } ?>placeholder="Instructor Name" /><br />
                <input name="course_description" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['course_description'])); ?>" <?php } ?>placeholder="Course Description" /><br />
                <input name="course_name" <?php if ($error) { ?>value="<?php echo (htmlspecialchars($_POST['course_name'])); ?>" <?php } ?>placeholder="Course Name" /><br />
                <br />
                <br />
                <button type="submit">Submit</button>
                </form>
                <br/>
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
            else
            {
                $new_course = Course::create_course($course_code, $section_number, $instructor_email, $instructor_name, $course_description, $course_name);
                if ($new_course->course_id == null)
                {
                    renderCreateCoursePage("Something went wrong, please try again");
                }
                else
                {
                    renderCreateCoursePage();
                }
            }
        } else {
    ?>
        <?php //include('common/header.php'); ?>
        <?php renderCreateCoursePage() ?>
    <?php } ?>
    <?php include('common/footer.php'); ?>
    <?php }
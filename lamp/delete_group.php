<?php
$page_title = "Delete Group";
?>
<?php require_once("lib/page-setup.php"); ?>
<?php include('lib/channel-handler.php'); ?>
<?php include('lib/course-handler.php');  ?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "GET") { ?>
    <?php
    if (!isset($_GET['ch_id'])) {
        header("Location: courses.php");
        die();
    }
    $channel_id = $_GET["ch_id"];
    echo($channel_id);
    $channel = new Channel($channel_id);
    $course = new Course($channel->course_id);
    $is_instructor = is_user_instructor($course->course_id);

    if (!$is_instructor && ($channel->type != 2))
    {
        header("Location: courses.php");
        die();
    }
    else
    {
        Channel::delete_channel($channel_id);
        header("Location: channels.php?ch_id=" . $course->course_id);
        die();
    }
} ?>


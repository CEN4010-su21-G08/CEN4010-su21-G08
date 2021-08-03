<?php
require_once("lib/course-handler.php");

global $user;
$courses;
if ($user->is_admin()) {
    $courses = Course::get_all_courses();
} else {
    $all_courses = CourseMembership::get_user_courses($user->uid);
    $courses = [];
    foreach ($all_courses as $course) {
        if ($course->is_instructor()) {
            $courses[] = $course;
        }
    }

    if (count($courses) <= 0) { ?>
        <div class="alert alert-danger">
            You do not have access to this page.
        </div>
<?php die();
    }
}

$current_course_id = null;
$current_course = null;
if (isset($_GET['ch_id']) && !empty($_GET['ch_id'])) {
    $current_course_id = $_GET['ch_id'];
    $current_course = new Course($current_course_id);
}
?>
<?php /* Form to select course to review reports for */ ?>
<div>
    Select a course to view reports for:
    <select name="courses" id="course_list">
        <option <?= $current_course_id && $current_course->course_id != null ? "" : "selected "; ?>disabled value="none">Select a course</option>
        <?php foreach ($courses as $course) { ?>
            <option <?= $current_course_id == $course->course_id ? "selected " : "" ?>value="<?= htmlspecialchars($course->course_id); ?>"><?= htmlspecialchars($course->course_code); ?>-<?= htmlspecialchars($course->section_number); ?></option>
        <?php } ?>
    </select>
</div>
<script>
    let search_params = new URLSearchParams(window.location.search);

    let $course_selector = $('#course_list');
    $course_selector.change(event => {

        if (search_params.get("ch_id") != $course_selector.val()) {
            window.location.href = "reports.php?ch_id=" + encodeURIComponent($course_selector.val());
        }

    });
</script>
<br />
<br />
<?php
if (isset($_GET['ch_id']) && !empty($_GET['ch_id'])) {
    if ($current_course->course_id == null) { ?>
        <div class="alert alert-danger">
            Invalid course. Please choose a course in the dropdown above.
        </div>
    <?php } else {
        $reports = Report::list_by_courseReports($current_course->course_id); ?>
        <table style="text-align: left;" class="table table-hover table-striped table-bordered">
            <thead class="table-light sticky-top">
                <th scope="col">Report Information</th>
                <th scope="col">Report Reason</th>
                <th scope="col">Reported Message</th>
                <th scope="col">More options</th>
            </thead>
            <tbody>
                <?php if (count($reports) == 0) { ?>
                    <tr>
                        <td style="text-align: center;" colspan="5">No reports found</td>
                    </tr>
                <?php } ?>
                <?php foreach ($reports as $report) { ?>
                    <tr>
                        <td scope="row">
                            Reported name: <?= htmlspecialchars($report->reported_user->first_name . " " . $report->reported_user->last_name); ?>
                            <br />Reporter name: <?= htmlspecialchars($report->reporter_user->first_name . " " . $report->reporter_user->last_name); ?>
                            <br />Date and Time: <?= htmlspecialchars($report->report_date); ?>
                            <?php
                            $ch_name = "";
                            $course_name = $report->channel_info->course_code . "-" . $report->channel_info->course_section_number;
                            // course
                            if ($report->channel_info->ch_id == $report->channel_info->ch_course_id) {
                                // $ch_name = $report->channel_info->course_code . "-" . $report->channel_info->course_section_number;
                                $ch_name = "Course Chat";
                            } else if (!empty($report->channel_info->ch_name)) {
                                // group with name
                                $ch_name = $report->channel_info->ch_name;
                            } else {
                                $ch_name = "Unknown Channel";
                            }

                            ?>
                            <br />Course: <?= htmlspecialchars($course_name); ?>
                            <br />Channel: <a href="channels.php?ch_id=<?= urlencode(htmlspecialchars($report->channel_info->ch_id)); ?>"><?= htmlspecialchars($ch_name); ?></a>
                        </td>
                        <td>
                            <?= htmlspecialchars($report->reason); ?>
                        </td>
                        <td style="max-width: 500px;">
                            <?= htmlspecialchars($report->message); ?>
                        </td>
                        <td>
                            <a class="btn btn-secondary" href="reports.php?r_id=<?= urlencode(htmlspecialchars($report->r_id)); ?>">View Detailed Report</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php }
} else { ?>
    <div class="alert alert-info">
        Please choose a course in the dropdown above.
    </div>
<?php }

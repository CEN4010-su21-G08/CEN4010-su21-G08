<?php
require_once("lib/course-handler.php");

global $user;

if (!isset($_GET['r_id']) || empty($_GET['r_id'])) {
    header("Location: reports.php");
    die();
}

$r_id = $_GET['r_id'];
$report = new Report($r_id);
$courseM = new CourseMembership($user->uid, $report->course_id);


if (!$user->is_admin() && ($courseM->role != 2)) {
    header("Location: reports.php");
    die();
}

?>

<div class="list-group" style="text-align: left;">
    <div class="list-group-item">
        <div>
            <h5 class="mb-1">Report Information</h5>
        </div>
        <p class="mb-1">
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
            Reported name: <?= htmlspecialchars($report->reported_user->first_name . " " . $report->reported_user->last_name); ?>
            <br />Reporter name: <?= htmlspecialchars($report->reporter_user->first_name . " " . $report->reporter_user->last_name); ?>
            <br />Date and Time: <?= htmlspecialchars($report->report_date); ?>
            <br />Course: <?= htmlspecialchars($course_name); ?>
            <br />Channel: <a href="channels.php?ch_id=<?= urlencode(htmlspecialchars($report->channel_info->ch_id)); ?>"><?= htmlspecialchars($ch_name); ?></a>
        </p>
    </div>
    <div class="list-group-item">
        <div>
            <h5 class="mb-1">Report Reason</h5>
        </div>
        <p class="mb-1">
            <?= htmlspecialchars($report->reason); ?>
        </p>
    </div>
    <div class="list-group-item">
        <div>
            <h5 class="mb-1">Reported Message</h5>
        </div>
        <p class="mb-1">
            <?= htmlspecialchars($report->message); ?>
        </p>
    </div>
    <div class="list-group-item">
        <div>
            <h5 class="mb-1">Actions</h5>
        </div>
        <br />
        <div class="d-grid gap-2">
            <button <?= $report->flags & 1 << 0 ? "disabled " : ""; ?>data-action="ignore" class="action-btn btn btn-outline-secondary">Ignore Report</button>
            <button <?= $report->flags & 1 << 1 ? "disabled " : ""; ?>data-action="delete" class="action-btn btn btn-outline-secondary">Delete Message</button>
            <button <?= $report->flags & 1 << 2 ? "disabled " : ""; ?>data-action="mute" class="action-btn btn btn-outline-secondary">Mute User</button>
            <button <?= $report->flags & 1 << 3 ? "disabled " : ""; ?>data-action="remove" class="action-btn btn btn-outline-secondary">Remove Student from Course</button>
            <?php if ($user->is_admin()) { ?>
                <button <?= $report->flags & 1 << 4 ? "disabled " : ""; ?>data-action="ban" class="action-btn btn btn-outline-danger">Ban User</button>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    $('.action-btn').click(event => {
        let $t = $(event.target);
        let action = $t.attr("data-action");
        let search = new URLSearchParams(window.location.search);
        $.post("reports.php?action=act&act=" + encodeURIComponent(action) + "&r_id=" + encodeURIComponent(search.get("r_id")))
            .then((data, status, xhr) => {
                window.location.reload();
            }, (xhr, status) => {
                alert("Something went wrong");
            });
    });
</script>
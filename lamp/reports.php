<?php
$page_title = "Reports";
$center_page = true;
?>
<?php require_once("lib/page-setup.php") ?>
<?php require_once("lib/report-handler.php"); ?>
<?php include('common/header.php'); ?>
<?php
$report = new Report("123");
$report->delete();
Report::list_courseReports("abc");
?>
<div>
    <?php /* Just demoing things */ ?>
    <p>Report ID: <code><?= htmlspecialchars($report->r_id); ?></code></p>
    <p>Reason: <code><?= htmlspecialchars($report->reason); ?></code></p>
    <!-- <p>Reason URL: <?= urlencode($report->reason); ?></p> -->
</div>

<?php include('common/footer.php'); ?>
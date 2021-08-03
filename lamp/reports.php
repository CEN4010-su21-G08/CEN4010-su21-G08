<?php
$page_title = "Reports";
$center_page = true;
?>
<?php require_once("lib/page-setup.php") ?>
<?php require_once("lib/report-handler.php"); ?>
<?php require_once("lib/course-handler.php"); ?>
<?php require_once("lib/channel-handler.php"); ?>
<?php
/**
 * POST
 *  ?action=
 *      create: create a report on a message
 *      act: perform an action on a report (delete message, ignore, etc)
 *      
 * GET
 *  ?action=
 *      list (or blank): list reports, possibly within a course
 *      get: get details about a single report
 */

?>
<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    header("Content-Type: application/json");
    $response = [];
    function sendResponse()
    {
        global $response;
        echo json_encode($response);
        die();
        return;
    }
    function sendError($error, $status = 500)
    {
        global $response;
        $response = [];
        $response['error'] = $error;
        $response['status'] = $status;
        http_response_code($status);
        sendResponse();
    }


    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        if ($action == "create") {
            // create a report from the user

            // first make sure the request body is valid
            // we need a message ID, channel ID, and a reason
            if (!isset($_POST['m_id']) || empty($_POST['m_id'])) {
                sendError("No message reported", 400);
            }
            if (!isset($_POST['ch_id']) || empty($_POST['ch_id'])) {
                sendError("Mismatched channel provided", 400);
            }
            if (!isset($_POST['reason']) || empty($_POST['reason'])) {
                sendError("No report reason provided", 400);
            }
            $m_id = $_POST['m_id'];
            $ch_id = $_POST['ch_id'];
            $reason = $_POST['reason'];

            $channel = new Channel($ch_id);
            if ($channel->ch_id == null) {
                sendError("Unknown Channel", 400);
            }
            if ($channel->type == 2) {
                $course_ch = new Channel($channel->course_id);
                if ($course_ch->ch_id == null) {
                    sendError("Unknown Channel", 400);
                }
                if ($course_ch->get_role() == 0) {
                    sendError("Forbidden", 403);
                }
            } else {
                if ($channel->get_role() == 0) {
                    sendError("Forbidden", 403);
                }
            }
            
            $message = Message::get($m_id);
            if ($message->ch_id == null || $message->ch_id !== $ch_id) {
                sendError("Mismatched channel provided", 400);
            }

            $create_response = Report::create($reason, $message->m_id, $channel->ch_id);
            if (isset($create_response['error'])) {
                if (isset($create_response['status'])) {
                    http_response_code($create_response['status']);
                }
                sendError($create_response['error'], $create_response['status']);
            }
            $response['success'] = true;
            sendResponse();
        } else if ($action == "act") {
        } else {
            sendError("Invalid action provided", 400);
        }
    } else {
        sendError("No action provided", 400);
    }
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    include('common/header.php');
    if (!isset($_GET['action']) || $_GET['action'] == 'list') {
        // List reports
        include('pages/report-list.php');
    } else {
        $action = $_GET['action'];
        if ($action == "get") {
        } else { ?>
            <div class="alert alert-danger">
                Invalid action
            </div>
<?php }
    }
    include('common/footer.php');
} else {
    http_response_code(400);
    echo "Invalid response";
}
?>

<?php ?>
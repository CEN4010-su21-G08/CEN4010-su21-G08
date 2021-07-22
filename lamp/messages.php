<?php
    $page_title = "Send Message";
?>
<?php
require_once("lib/page-setup.php");
require_once('lib/message-handler.php');
 ?>

<?php
    $announcement = isset($_GET['announcements']);
    $channel_id = parse_get_input("ch_id", true);
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        global $user;
        $message = parse_input('message', true);
        $res = Message::send($channel_id, $user->uid, $message, $announcement);
        header("Content-Type: application/json");
        echo(json_encode($res));
    } elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_GET['start_before']) && !empty($_GET['start_before'])) {
            $start_before = parse_get_input("start_before", true);
            $messages = Message::get_before($channel_id, $start_before, $announcement);
        } else {
            $start_after = parse_get_input("start_after", false);
            $messages = Message::get_after($channel_id, $start_after, $announcement);
        }

        header("Content-Type: application/json");

        // $messages = get_messages($start_after, true);
        echo(json_encode($messages));
    }
?>
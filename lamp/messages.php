<?php
    $page_title = "Send Message";
?>
<?php 
require_once("lib/page-setup.php");
require_once('lib/message-handler.php');
 ?>

<?php 
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $channel_id = parse_get_input("ch_id", true);
        send_message();
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
        $channel_id = parse_get_input("ch_id", true);
        $start_after = parse_get_input("start_after", false);

        $messages = get_messages($start_after, true);
        echo ($messages);
    }
?>
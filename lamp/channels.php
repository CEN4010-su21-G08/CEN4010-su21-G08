<?php
    $page_title = "Channels";
?>
<?php require_once("lib/page-setup.php") ?>
<?php include('lib/message-handler.php'); ?>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        //send_message();
    //} else {
?>
<?php 
    if (isset($_GET['ch_id'])) {
        $channel_id = $_GET["ch_id"];
    } else {
        header("Location: courses.php");
    }

    $has_access = does_user_have_access($_SESSION['uid'], $channel_id);
    

    ?>
<?php include('./common/header.php'); ?>
<?php
if (!$has_access) {?>
        <div class="alert alert-danger" style="margin: 20px;">You don't have access to this channel or it doesn't exist</div>
    <?php } else {?>
Channels
<div>
<h2>Test Channel</h2>
<div class="messages">
    <?php 
        $messages = get_messages();
        /*foreach($messages as $message) {
            // echo "<br />";
            // echo htmlspecialchars($message["message"]);
            ?>
                <div class="message" style="border: 1px solid black;margin: 5px; padding: 5px;">
                    <span class="message_id"><?php echo htmlspecialchars($message["m_id"]); ?></span>
                    <div class="message_content"><?php echo htmlspecialchars($message["message"]); ?></div>
                    <span="message_date"><?php echo htmlspecialchars($message["send_date"]); ?></span>
                </div>
            <?php
        }*/
    ?>
</div>
</div>
<form method="post" id="send_message_form" action="<?php echo(htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
    <input name="message" type="text" placeholder="message" /><br />
    </select><br />
    <button type="submit">Send Message</button>
</form>

<script>
    $('#send_message_form').click(event => {
        event.preventDefault();
        let data = $('#send_message_form').serialize();
        console.log(data);
        console.log(event.target);
        $.post("messages.php?ch_id=" + encodeURIComponent("<?php echo(htmlspecialchars($_GET['ch_id'])); ?>"), data, () => {
            // $('#send_message_form').children("input[name=message]").val("");
            console.log('sent');
            getNewMessages();
            
        })
    });
    <?php 
    if (count($messages) > 0) {
        $last_message_send_date = ($messages[count($messages) - 1])['send_date'];
    ?>
        // let last_message_date = "<?php //echo(htmlspecialchars($last_message_send_date)); ?>";
        //let last_message_date = null;
     <?php } else { ?>
        //let last_message_date = null;
     <?php } ?>
    //  let messages = `<?php //echo(htmlentities(json_encode($messages))); ?>`;
    let last_message_date = null;
    let messages = [];
    getNewMessages();

    setTimeout(() => {
        setInterval(() => {
            getNewMessages()
        }, 5000)
    }, 5000)
    function getNewMessages() {
               $.get("messages.php?ch_id=" + encodeURIComponent("<?php echo(htmlspecialchars($_GET['ch_id'])); ?>") + (last_message_date ? ("&start_after=" +  encodeURIComponent(last_message_date)) : ""), (data) => {
            console.log('did it work?')
            console.log(data);
            let newMessages = JSON.parse(data);
            for (newM of newMessages) {
                if (messages.find(m => m.m_id == newM.m_id) == undefined) {
                    messages.push(newM);
                }
            }
            messages.sort((a, b) => {
                return (new Date(a.send_date) >= new Date(b.send_date)) ? 1 : -1;
            })

            $('.messages').empty();

            if (messages.length == 0) {
                $('.messages').text("No messages found");
                last_message_date = null;
            } else {
                last_message_date = messages[messages.length - 1].send_date;
                messages.forEach(m => {
                    let $m_el = $('<div class="message"><div class="message_author"></div><div class="message_content"></div></div>');
                    $m_el.children(".message_content").text(m.message);
                    $m_el.children(".message_author").text(m.display_name);
                    $('.messages').append($m_el)
                })
                if (messages.length == 0) {
                    $('.messages').text("No messages found");
                }
            }
            
        });
    }
</script>
<?php } ?>
<?php include('common/footer.php'); ?>
<?php } ?>
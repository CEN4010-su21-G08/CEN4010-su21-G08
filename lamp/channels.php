<?php
$page_title = "Channels";
$include_sidebar = true;
?>
<?php require_once("lib/page-setup.php"); ?>
<?php include('lib/message-handler.php'); ?>
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
    $channel = new Channel($channel_id);
    $course = new Course($channel->course_id);


    $has_access = does_user_have_access($_SESSION['uid'], $channel_id);
    include('./common/header.php');
    if (!$has_access) { ?>
        <div class="alert alert-danger" style="margin: 20px;">You don't have access to this channel or it doesn't exist</div>
    <?php } else {
        $groups = Channel::get_users_channels_in_course($_SESSION['uid'], $channel->course_id, true);
    ?>
        <?php show_sidebar("Course", $course->course_code . "-" . $course->section_number, $channel->course_id, $groups); ?>

        <div class="channels_main">
            <div>
                <h2>
                    <?php
                    if (!isset($channel->name)) {
                        echo ($course->course_code . '-' . $course->section_number . " Main Chat");
                    } else {
                        echo (htmlspecialchars($channel->name));
                    }
                    ?>
                </h2>
                <div class="older">
                    <button id="older-btn" class="btn btn-outline-secondary" onclick="getOlderMessages();">Load earlier messages</button>
                </div>
                <div class="messages"></div>
            </div>
            <form method="post" id="send_message_form" action="<?php echo (htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
                <div class="form-group">
                    <input class="form-control" name="message" type="text" placeholder="Message" />
                    <button style="margin-top: 5px;" class="btn btn-primary" type="submit">Send Message</button>
                </div>
            </form>

            <script>
                $send_input = $("input[name=message]");
                $send_button = $("button[type=submit]")
                $('#send_message_form').submit(event => {
                    event.preventDefault();
                    let data = $('#send_message_form').serialize();
                    console.log(data);
                    console.log(event.target);

                    $send_input.addClass("disabled");
                    $send_input.attr("disabled", "disabled");
                    $send_button.addClass("disabled");
                    $send_button.attr("disabled", "disabled");
                    $send_button.text("Sending...");

                    $('#send_message_form').children("input[name=message]").addClass("disabled");
                    $.post("messages.php?ch_id=" + encodeURIComponent("<?php echo (htmlspecialchars($_GET['ch_id'])); ?>"), data, () => {
                        $send_input.val("");
                        getNewMessages(() => {
                            $send_input.removeClass("disabled");
                            $send_input.attr("disabled", null);
                            $send_button.removeClass("disabled");
                            $send_button.attr("disabled", null);
                            $send_button.text("Send Message");
                        });

                    })
                });
                let last_message_id = null;
                let messages = [];
                getNewMessages();

                setTimeout(() => {
                    setInterval(() => {
                        getNewMessages()
                    }, 5000)
                }, 5000);
                $older_btn = $("#older-btn");

                function noOlderMessages() {
                    console.log("no older messages");
                    $older_btn.addClass("disabled");
                    $older_btn.text("No more messages to load")
                }

                function getOlderMessages() {
                    $older_btn.addClass("disabled");
                    $older_btn.text("Loading...")
                    let oldest_message = messages[0];
                    if (!oldest_message) {
                        return noOlderMessages();
                    }
                    let oldest_message_id = oldest_message.m_id;

                    $.get("messages.php?ch_id=" + encodeURIComponent("<?php echo (htmlspecialchars($_GET['ch_id'])); ?>") + ("&start_before=" + encodeURIComponent(oldest_message_id)), (data) => {
                        console.log('did it work? older')
                        console.log(data);
                        // let newMessages = JSON.parse(data);
                        if (data.error) {
                            $('.messages').empty();
                            $('.messages').text("Could not get older messages: " + data.error);
                            return;
                        }
                        let newMessages = data;
                        console.log(newMessages.length);
                        if (newMessages.length == 0) {
                            return noOlderMessages();
                        }
                        for (newM of newMessages) {
                            if (messages.find(m => m.m_id == newM.m_id) == undefined) {
                                messages.push(newM);
                            }
                        }
                        messages.sort((a, b) => {
                            return (new Date(a.send_date) >= new Date(b.send_date)) ? 1 : -1;
                        });

                        getNewMessages(() => {
                            $older_btn.removeClass("disabled");
                            $older_btn.text("Load earlier messages")
                        });

                    });
                }

                function getNewMessages(done) {
                    $.get("messages.php?ch_id=" + encodeURIComponent("<?php echo (htmlspecialchars($_GET['ch_id'])); ?>") + (last_message_id ? ("&start_after=" + encodeURIComponent(last_message_id)) : ""), (data) => {
                        console.log('did it work?')
                        console.log(data);
                        // let newMessages = JSON.parse(data);
                        if (data.error) {
                            $('.messages').empty();
                            $('.messages').text("Could not get new messages: " + data.error);
                            return;
                        }
                        let newMessages = data;
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
                            last_message_id = null;
                        } else {
                            last_message_id = messages[messages.length - 1].m_id;
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
                        if (typeof done == 'function') {
                            done();
                        }
                    });
                }
            </script>
        <?php } ?>
        </div>
        <?php include('common/footer.php'); ?>
    <?php } ?>
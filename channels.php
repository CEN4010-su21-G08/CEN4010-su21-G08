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
    $is_instructor = is_user_instructor($course->course_id);

    $is_primary_course_chat = $course->course_id == $channel_id;
    $announcement = isset($_GET['announcements']) && $is_primary_course_chat;


    $has_access = does_user_have_access($_SESSION['uid'], $channel_id);
    include('./common/header.php');
    if (!$has_access) { ?>
        <div class="alert alert-danger" style="margin: 20px;">You don't have access to this channel or it doesn't exist</div>
    <?php } else {
        $groups = Channel::get_users_channels_in_course($user->uid, $channel->course_id, true);
        if ($channel->type == 2) {
            $members = Channel::get_members($channel->ch_id);
        } else {
            $members = [];
        }

    ?>
        <?php
        show_sidebar("Course", $course->course_code . "-" . $course->section_number, $channel->course_id, $channel->name, $groups, $is_instructor, $members);
        ?>
        <div class="channels_main">
            <div>
                <h2>
                    <?php
                    if (!isset($channel->name)) {
                        echo ($course->course_code . '-' . $course->section_number . (isset($_GET['announcements']) ? " Announcements" : " Main Chat"));
                    } else {
                        echo (htmlspecialchars($channel->name));
                    }
                    ?>
                </h2>
                <?php
                if ($is_instructor) { ?>
                    <h2>INSTRUCTOR VIEW</h2>
                    <?php
                    if ($channel->type == 2) { ?>
                        <a href="manage-group.php?ch_id=<?=$channel->ch_id?>">Manage Group</a>
                    <?php } ?>
                <?php } ?>
                <div class="older">
                    <button id="older-btn" class="btn btn-outline-secondary" onclick="getOlderMessages();">Load earlier messages</button>
                </div>
                <div class="messages"></div>
            </div>
            <?php if (!$announcement || ($announcement && $is_instructor)) { ?>
                <form method="post" id="send_message_form" action="<?php echo (htmlspecialchars($_SERVER['PHP_SELF'])); ?>">
                    <div class="form-group">
                        <div id="message_send_validation_group" class="has-validation">
                            <input class="form-control" name="message" maxlength="165" type="text" placeholder="<?= $announcement ? "Announcement" : "Message" ?>" />
                            <div class="invalid-feedback">
                                Could not send message.
                            </div>
                        </div>
                        <?php if ($is_primary_course_chat && $is_instructor && !$announcement) { ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" name="send_as_announcement" id="send_as_announcement">
                                <label class="form-check-label" for="send_as_announcement">
                                    Send as announcement
                                </label>
                            </div>
                        <?php } ?>

                        <button style="margin-top: 5px;" class="btn btn-primary" type="submit">Send <?= $announcement ? "Announcement" : "Message" ?></button>
                    </div>
                </form>
            <?php } ?>

            <div class="" style="display: none;" id="context_menu">
                <a class="list-group-item list-group-item-action context_menu_link" data-action='report' href="#">Report Message</a>
            </div>
            <script>
                <?php if (!$announcement || ($announcement && $is_instructor)) { ?>
                    $send_input = $("input[name=message]");
                    $send_button = $("button[type=submit]");
                    <?php if (!$announcement && $is_instructor) { ?>
                        $send_as_announcement = $("input[type=checkbox]");
                    <?php } ?>
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
                        <?php if (!$announcement && $is_instructor) { ?>
                            $send_as_announcement.addClass("disabled");
                            $send_as_announcement.attr("disabled", "disabled");
                            $is_announcement = $send_as_announcement.is(":checked");
                        <?php } ?>


                        $('#send_message_form').children("input[name=message]").addClass("disabled");
                        $.post("messages.php?<?php if ($is_primary_course_chat && !$announcement && $is_instructor) { ?>" + ($is_announcement ? "announcements&" : "") + "<?php } ?><?= ($announcement && $is_instructor) ? "announcements&" : "" ?>ch_id=" + encodeURIComponent("<?php echo (htmlspecialchars($_GET['ch_id'])); ?>"), data, () => {
                            $send_input.val("");
                            getNewMessages(() => {
                                $send_input.removeClass("disabled");
                                $send_input.attr("disabled", null);
                                $send_button.removeClass("disabled");
                                $send_button.attr("disabled", null);
                                $send_button.text("Send Message");
                                <?php if (!$announcement && $is_instructor) { ?>
                                    $send_as_announcement.prop("checked", false);
                                    $send_as_announcement.removeClass("disabled");
                                    $send_as_announcement.attr("disabled", null);
                                <?php } ?>
                            });

                        }).fail((xhr, status, error) => {
                            console.log(xhr, status, error)
                            $send_input.addClass("is-invalid");
                            let errorText = "Could not send your message";
                            if (xhr.responseJSON && xhr.responseJSON['error']) {
                                errorText = "Could not send your message: " + xhr.responseJSON['error'];
                                if (xhr.responseJSON['error'] == 'Message too long') {
                                    errorText += " (max of 165 characters)";
                                }
                            }
                            $("#message_send_validation_group .invalid-feedback").text(errorText);
                            $send_input.removeClass("disabled");
                            $send_input.attr("disabled", null);
                            $send_button.removeClass("disabled");
                            $send_button.attr("disabled", null);
                            $send_button.text("Send Message");
                            <?php if (!$announcement && $is_instructor) { ?>
                                $send_as_announcement.prop("checked", false);
                                $send_as_announcement.removeClass("disabled");
                                $send_as_announcement.attr("disabled", null);
                            <?php } ?>
                        })
                    });

                <?php } ?>

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

                    $.get("messages.php?<?= $announcement ? 'announcements&' : ''; ?>ch_id=" + encodeURIComponent("<?php echo (htmlspecialchars($_GET['ch_id'])); ?>") + ("&start_before=" + encodeURIComponent(oldest_message_id)), (data) => {
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
                    $.get("messages.php?<?= $announcement ? 'announcements&' : ''; ?>ch_id=" + encodeURIComponent("<?php echo (htmlspecialchars($_GET['ch_id'])); ?>") + (last_message_id ? ("&start_after=" + encodeURIComponent(last_message_id)) : ""), (data) => {
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
                                let $m_el = $('<div class="message"><div class="message_author"></div><span class="empty_m_badge"></span><div class="message_content"></div></div>');
                                $m_el.children(".message_content").text(m.message);
                                $m_el.children(".message_author").text(m.display_name);
                                if (m.flags) {
                                    if (parseInt(m.flags) & (1 << 0)) {
                                        let $badge = $m_el.children(".empty_m_badge")
                                        $badge.addClass("badge bg-primary announcement");
                                        $badge.text("Announcement");
                                        $badge.removeClass("empty_m_badge");

                                        $m_el.addClass("announcement-message");
                                    }
                                }

                                $m_el.attr("data-m_id", m.m_id);

                                $('.messages').append($m_el)
                            })
                            if (messages.length == 0) {
                                $('.messages').text("No messages found");
                            }
                        }
                        $('.message').off('contextmenu');
                        $('.message').on('contextmenu', messageContextMenuHandler);
                        if (typeof done == 'function') {
                            done();
                        }
                    });
                }


                /* Right-click handler */
                function messageContextMenuHandler(event) {
                    event.preventDefault();

                    let $m = $(this);
                    let m_id = $m.attr("data-m_id");

                    $contextMenu = $('#context_menu');
                    $contextMenu.attr("data-message", m_id);
                    $contextMenu.css('display', "block");
                    $contextMenu.css('left', event.pageX + 2)
                    $contextMenu.css('top', event.pageY + 2)
                }

                function closeContextMenu() {
                    $contextMenu = $('#context_menu');
                    $contextMenu.attr("data-message", "");
                    $contextMenu.css('display', "none");
                    $contextMenu.css('left', 0)
                    $contextMenu.css('top', 0)
                }

                // If the context menu is open, close it if a click occurs outside the menu
                $(document.body).click(event => {
                    $cm = $('#context_menu');
                    if ($cm.css('display') == 'block' && !$cm[0].contains(event.target)) {
                        closeContextMenu();
                    }
                });
                // same, but for the context menu. also check that it's not a message
                $(document.body).contextmenu(event => {
                    let cT = event.target;
                    if ($(cT).hasClass("message")) return;
                    let $cTParent = $(cT).parent(".message");
                    if ($cTParent.length > 0) return;

                    $cm = $('#context_menu');
                    if ($cm.css('display') == 'block' && !$cm[0].contains(event.target)) {
                        closeContextMenu();
                    }
                });

                $(".context_menu_link").click(event => {
                    let $target = $(event.target);
                    let action = $target.attr('data-action');
                    let message = $target.parent("#context_menu").attr("data-message");
                    showReportModal(message);
                    closeContextMenu();
                    switch (action) {
                        case 'report':
                            console.log("report!")
                            break;
                        default:
                            break;
                    }
                });
                let $modal = null;
                let bsModal = null;

                function showReportModal(m_id) {
                    $modal = createModal();
                    let $modalBody = $($modal[0].querySelector(".modal-body"));
                    $modalBody.text("Are you sure you want to report?");
                    let $form = $("<form method='post'><input id='report_reason' placeholder='report reason' type='text' name='reason' /></form>")
                    $form.on('submit', event => {
                        event.preventDefault();
                        $reason = $('#report_reason');
                        submitReport(m_id, $reason.val());
                    })
                    $modalBody.append($form);
                    let $modalHeader = $($modal[0].querySelector("h5"));
                    $modalHeader.text("Report Message");
                    bsModal = new bootstrap.Modal($modal);
                    bsModal.show();


                }

                function submitReport(m_id, reason) {
                    let urlSearchParams = new URLSearchParams(window.location.search);
                    let ch_id = urlSearchParams.get("ch_id");
                    $.post("reports.php?action=create", {
                        m_id: m_id,
                        ch_id: ch_id,
                        reason: reason,
                    }).then((data, status, xhr) => {
                        if (status == 'success') {
                            if (xhr.getResponseHeader("Content-Type") != 'application/json' || !data) {
                                handleReportError();
                                return;
                            }
                            if (data.error) {
                                handleReportError(data.error);
                                return;
                            }
                            bsModal.dispose();
                            $modal.remove();

                            $modal = createModal();
                            let $modalBody = $($modal[0].querySelector(".modal-body"));
                            $modalBody.html("<div class='alert alert-success'>Successfully submitted report!</div>");
                            let $modalHeader = $($modal[0].querySelector("h5"));
                            $modalHeader.text("Submitted report")

                            let $saveBtn = $($modal[0].querySelector(".btn-primary"));
                            $saveBtn.remove();
                            let $closeBtn = $($modal[0].querySelector(".btn-secondary"));
                            $closeBtn.addClass("btn-primary");
                            $closeBtn.removeClass("btn-secondary");
                            bsModal = new bootstrap.Modal($modal);
                            bsModal.show();
                        }
                        console.log(status);
                    }, (xhr, status, error) => {
                        if (xhr.responseJSON && xhr.responseJSON.error)
                            handleReportError(xhr.responseJSON.error);
                        else {
                            handleReportError();
                        }

                    })
                }

                function handleReportError(error = "An unknown error occurred") {
                    let $modalBody = $($modal[0].querySelector(".modal-body"));
                    if ($modalBody[0].querySelector(".alert")) {
                        $($modalBody[0].querySelector(".alert")).remove();
                    }
                    let $alert = $("<div style='padding-top: 10px;' class='alert alert-danger'>Something went wrong: <span></span></div>");
                    $($alert[0].querySelector("span")).text(error);
                    $modalBody.append($alert);
                }


                function createModal() {
                    let $m = $('<div data-bs-backdrop="static" class="modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="button" class="btn btn-primary">Save changes</button></div></div></div></div>');
                    return $m;
                }
            </script>
        <?php } ?>
        </div>
        <?php include('common/footer.php'); ?>
    <?php } ?>
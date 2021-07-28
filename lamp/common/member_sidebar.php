<?php
// Sidebar from https://getbootstrap.com/docs/5.0/examples/sidebars/# 
function show_member_sidebar($channel_type, $is_instructor, $members, $ch_id) {
    global $member_sidebar_shown;
    global $center_page;
    $member_sidebar_shown = true;

    $active_page = null;
    // if ()
?>
    <div class="right_sidebar">
        <div class="flex-shrink-0 p-3 bg-white" style="width: 280px;">
            <span class="fs-5 fw-semibold">Members</span>
            <?php
            foreach ($members as $member)
            { ?>
                <br />
                <?php
                echo($member->first_name);
                echo(" ");
                echo($member->last_name);
            }
            ?>
            <br />
            <?php
            if ($channel_type == 2 && $is_instructor)
            { ?>
                <a href="delete_group.php?ch_id=<?php echo($ch_id); ?>">Delete Group</a>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="main-content<?php if (isset($center_page)) { ?> main-content-center<?php } ?>">

    <?php } ?>
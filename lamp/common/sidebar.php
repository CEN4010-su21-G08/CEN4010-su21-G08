<?php
// Sidebar from https://getbootstrap.com/docs/5.0/examples/sidebars/# 
function show_sidebar($heading, $course_name, $course_id, $groups, $is_instructor) {
    global $sidebar_shown;
    global $center_page;
    $sidebar_shown = true;
?>
    <div class="sidebar">
        <div class="flex-shrink-0 p-3 bg-white" style="width: 280px;">
            <a href="#" class="d-flex align-items-center pb-3 mb-3 link-dark text-decoration-none border-bottom">
                <svg class="bi me-2" width="30" height="24">
                    <use xlink:href="#bootstrap" />
                </svg>
                <span class="fs-5 fw-semibold"><?php echo htmlspecialchars($heading); ?> </span>
            </a>
            <ul class="list-unstyled ps-0">
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#home-collapse" aria-expanded="true">
                        <?php echo htmlspecialchars($course_name); ?>
                    </button>
                    <div class="collapse show" id="home-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="course-info.php?course_id=<?= urlencode($course_id); ?>" class="link-dark rounded">About Course</a></li>
                            <li><a onclick="return false" href="channels.php?announcements&ch_id=<?= urlencode($course_id); ?>" class="link-dark rounded disabled">Announcements</a></li>
                            <li><a href="channels.php?ch_id=<?= urlencode($course_id); ?>" class="link-dark rounded">Course Chat</a></li>
                        </ul>
                    </div>
                </li>
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#dashboard-collapse" aria-expanded="false">
                        Groups
                    </button>
                    <div class="collapse" id="dashboard-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <?php if (count($groups) >= 1) { 
                                foreach($groups as $group) {
                                    ?> 
                                    <li><a href="channels.php?ch_id=<?= urlencode($group->ch_id); ?>" class="link-dark rounded"><?= $group->name; ?></a></li>
                                <?php } ?>
                            <?php } else { ?>
                                <li><span class="sidebar-no-link">No groups</span></li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>
                <?php
                if ($is_instructor)
                { ?>
                <li class="mb-1">
                    <a href="create-group.php?course_id=<?= urlencode($course_id);?>" class="link-dark rounded">Create Group</a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="main-content<?php if (isset($center_page)) { ?> main-content-center<?php } ?>">

    <?php } ?>
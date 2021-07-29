<?php
// Sidebar from https://getbootstrap.com/docs/5.0/examples/sidebars/# 
function show_sidebar($heading, $course_name, $course_id, $groups, $is_instructor, $members = [])
{
    global $sidebar_shown;
    global $center_page;
    $sidebar_shown = true;

    $active_page = null;
    // if ()
    $url_parts = explode("/", $_SERVER["PHP_SELF"]);
    $filename = $url_parts[count($url_parts) - 1];

    if ($filename == 'course-info.php' && isset($_GET['ch_id']) && $course_id == $_GET['ch_id']) {
        $active_page = 'course-info';
    } else if ($filename == 'channels.php' && isset($_GET['announcements']) && isset($_GET['ch_id']) && $course_id == $_GET['ch_id']) {
        $active_page = 'announcements';
    } else if ($filename == 'channels.php' && isset($_GET['ch_id']) && $course_id == $_GET['ch_id']) {
        $active_page = 'chat';
    }
?>
    <div class="sidebar">
        <div class="flex-shrink-0 p-3 bg-white" style="width: 280px;">
            <a href="channels.php?ch_id=<?= urlencode(htmlspecialchars($course_id)) ?>" class="d-flex align-items-center pb-3 mb-3 link-dark text-decoration-none border-bottom">
                <span class="fs-5 fw-semibold"><?php echo htmlspecialchars($heading); ?> </span>
            </a>
            <ul class="list-unstyled ps-0">
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#home-collapse" aria-expanded="true">
                        <?php echo htmlspecialchars($course_name); ?>
                    </button>
                    <div class="collapse show" id="home-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="course-info.php?course_id=<?= urlencode($course_id); ?>" class="<?= $active_page == 'course-info' ? 'active ' : '' ?>link-dark rounded">About Course</a></li>
                            <li><a href="channels.php?announcements&ch_id=<?= urlencode($course_id); ?>" class="<?= $active_page == 'announcements' ? 'active ' : '' ?>link-dark rounded disabled">Announcements</a></li>
                            <li><a href="channels.php?ch_id=<?= urlencode($course_id); ?>" class="<?= $active_page == 'chat' ? 'active ' : '' ?>link-dark rounded">Course Chat</a></li>
                        </ul>
                    </div>
                </li>
                <li class="mb-1">
                    <button class="btn btn-toggle align-items-center rounded collapsed" data-bs-toggle="collapse" data-bs-target="#dashboard-collapse" aria-expanded="true">
                        Groups
                    </button>
                    <div class="collapse show" id="dashboard-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <?php if (count($groups) >= 1) {
                                foreach ($groups as $group) {
                            ?>
                                    <li><a href="channels.php?ch_id=<?= urlencode($group->ch_id); ?>" class="<?= $active_page == null && isset($_GET['ch_id']) && $group->ch_id == $_GET['ch_id'] ? 'active ' : '' ?>link-dark rounded"><?= $group->name; ?></a> <a href="#" data-ch-id="<?= htmlspecialchars($group->ch_id) ?>" class="rounded-circle c-menu-icon c-show-members">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="rounded-circle bi bi-people-fill" viewBox="0 0 16 16">
                                                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                                <path fill-rule="evenodd" d="M5.216 14A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216z" />
                                                <path d="M4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
                                            </svg>
                                        </a></li>
                                <?php } ?>
                            <?php } else { ?>
                                <li><span class="sidebar-no-link">No groups</span></li>
                            <?php } ?>
                        </ul>
                    </div>
                </li>
                <?php
                if ($is_instructor) { ?>
                    <li class="mb-1">
                        <a href="create-group.php?course_id=<?= urlencode($course_id); ?>" class="link-dark rounded">Create Group</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div id="myModal" class="modal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Modal body text goes here.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(".c-show-members").click(event => {
            let $t = $(event.target);
            let ch_id = $t.attr('data-ch-id');
            let is_current = $t.siblings().hasClass("active");

            if (!is_current) {
                console.log('not current');
                window.location.href = "channels.php?ch_id=" + encodeURIComponent(ch_id) + "&members"
            } else {
                console.log('current');
                new bootstrap.Modal(document.getElementById('myModal'), {}).show();
            }
        });
    </script>
    <div class="main-content<?php if (isset($center_page)) { ?> main-content-center<?php } ?>">

    <?php } ?>
<?php
$page_title = "My Courses";
$center_page = true;
?>
<?php require_once("lib/page-setup.php") ?>
<?php require_once("lib/course-handler.php") ?>
<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    header("Content-Type: application/json");
    $response = array();
    function sendError($message)
    {
        global $response;
        $response = ["error" => $message];
        sendResponse();
    }

    function sendResponse()
    {
        global $response;
        echo (json_encode($response));
        die();
    }
    if (isset($_POST['course_id']) && !empty($_POST['course_id'])) {
        if (!CourseMembership::is_user_member($user->uid, $_POST['course_id'])) {
            CourseMembership::create_membership($user->uid, $_POST['course_id']);
            $response['success'] = true;
            sendResponse();
        } else {
            sendError("You're already a member of that course.");
        }
    } else {
        sendError("Missing ID of course to join.");
    }
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_GET['action']) && ($_GET['action'] == 'search')) {
        header("Content-Type: application/json");

        $response = array();

        function sendError($message)
        {
            global $response;
            $response = ["error" => $message];
            sendResponse();
        }

        function sendResponse()
        {
            global $response;
            echo (json_encode($response));
            die();
        }

        if (!isset($_GET['query']) || empty($_GET['query']) || !is_string($_GET['query'])) {
            sendError("Missing or invalid search parameters.");
        }

        $search = $_GET['query'];
        if (strlen($search) > 12) {
            sendResponse();
        }
        $search = explode("-", $search);
        if ($search == false) {
            sendResponse();
        }
        if (count($search) > 2 || count($search) < 1) {
            sendResponse();
        }
        if (strlen($search[0]) > 8) {
            sendResponse();
        }
        if (count($search) == 2 && strlen($search[1]) > 3) {
            sendResponse();
        }

        $cc = $search[0];
        $sn = count($search) == 2 ? $search[1] : "";

        $results = Course::search_course_names($cc, $sn);

        foreach ($results as $result) {
            $result->course_code = htmlspecialchars($result->course_code);
            $result->section_number = htmlspecialchars($result->section_number);
            $result->course_id = htmlspecialchars($result->course_id);
            $response[] = $result;
        }

        sendResponse();
    } else {
?>
        <?php include('common/header.php'); ?>
        <link rel="stylesheet" href="static/autocomplete.css" />
        <?php
        function course_list_show_course($course, $stu_only = null)
        {
            if ($stu_only === null || ($stu_only === false && $course->is_instructor()) || ($stu_only == true && !$course->is_instructor())) { ?>
                <a href="channels.php?ch_id=<?php echo (urlencode(htmlspecialchars($course->course_id))); ?>" class="list-group-item list-group-item-action"><?php echo (htmlspecialchars($course->course_code)); ?>-<?php echo (htmlspecialchars($course->section_number)); ?></a>
        <?php
            }
        }
        ?>
        <?php
        $courses = CourseMembership::get_user_courses($_SESSION['uid']);
        $course_types = ['instructor' => 0, 'student' => 0];
        foreach ($courses as $course) {
            if ($course->is_instructor()) $course_types['instructor']++;
            else $course_types['student']++;
        }
        // $courses[0]->role = 0;

        ?>
        Courses<br />
        <div id="join-course-error" style="display: none" class="alert alert-danger"></div>
        <form id="join-course-form" autocomplete="off" class="burrow-choose-course-form" method="post" action="">
            <span style="color: #cc0000;"></span>
            <div class="mb-3 mt-3 autocomplete" style="width: 300px;">
                <label for="courseCode">Search for courses</label>
                <input id="courseSearch" type="search" class="mt-1 form-control" name="courseCode" placeholder="Start typing... ex: CEN4010-001" id="courseCode" />
            </div>
            <input id="course_id" type="hidden" name="course_id" value="" />
            <br />
            <div>
                Selected course:
                <br />
                <b class="insertCourseName">Search for a course above</b>
            </div>
            <br />
            <input type="submit" value="Join course" class="btn btn-primary" />
        </form>
        <hr />
        <h2 class="h5">Your Courses</h2>
        <?php if ($course_types['student'] > 0) { ?>
            <div class="course-list">
                <h3 class="h6">Joined as a student:</h3>
                <div class="list-group">
                    <?php foreach ($courses as $course) {
                        course_list_show_course($course, true);
                    } ?>
                </div>
            </div>
        <?php }
        if ($course_types['instructor'] > 0) { ?>
            <div class="course-list">
                <h3 class="h6">Joined as an instructor:</h3>
                <div class="list-group">
                    <?php foreach ($courses as $course) {
                        course_list_show_course($course, false);
                    } ?>
                </div>
            </div>
        <?php } ?>
        <br />

        <script>
            let searchResults = [];

            let $searchInput = $("#courseSearch");
            let $searchCourseId = $('#course_id');


            // Based on w3schools's "How TO - Autocomplete"
            // https://www.w3schools.com/howto/howto_js_autocomplete.asp
            // Modified to fit the needs of this application
            function autocomplete(inp) {

                /*the autocomplete function takes two arguments,
                the text field element and an array of possible autocompleted values:*/
                var currentFocus;
                /*execute a function when someone writes in the text field:*/
                inp.addEventListener("input", function(e) {
                    let contents = $searchInput.val();
                    if (contents == '') {
                        console.log("(empty)")
                    } else {
                        $.get("courses.php", {
                            "action": "search",
                            "query": contents,
                        }, (data, textStatus, xhr) => {
                            if (textStatus == 'success') {
                                if (data['error']) {
                                    console.error("Error: ", data['error']);
                                } else {
                                    arr = data.map(r => {
                                        return {
                                            name: r['course_code'] + '-' + r['section_number'],
                                            id: r['course_id'],
                                        }
                                    });

                                    console.log(arr);



                                    var a, b, i, val = this.value;
                                    /*close any already open lists of autocompleted values*/
                                    closeAllLists();
                                    if (!val) {
                                        return false;
                                    }
                                    currentFocus = -1;
                                    /*create a DIV element that will contain the items (values):*/
                                    a = document.createElement("DIV");
                                    a.setAttribute("id", this.id + "autocomplete-list");
                                    a.setAttribute("class", "autocomplete-items");
                                    /*append the DIV element as a child of the autocomplete container:*/
                                    this.parentNode.appendChild(a);
                                    /*for each item in the array...*/
                                    for (i = 0; i < arr.length; i++) {
                                        /*check if the item starts with the same letters as the text field value:*/
                                        if (arr[i]['name'].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                                            /*create a DIV element for each matching element:*/
                                            b = document.createElement("DIV");
                                            /*make the matching letters bold:*/
                                            b.innerHTML = "<strong>" + arr[i]['name'].substr(0, val.length) + "</strong>";
                                            b.innerHTML += arr[i]['name'].substr(val.length);
                                            /*insert a input field that will hold the current array item's value:*/
                                            b.innerHTML += "<input type='hidden' value='" + arr[i]['name'] + "' data-course-id='" + arr[i]['id'] + "'>";
                                            /*execute a function when someone clicks on the item value (DIV element):*/
                                            b.addEventListener("click", function(e) {
                                                /*insert the value for the autocomplete text field:*/
                                                inp.value = this.getElementsByTagName("input")[0].value;
                                                $searchCourseId.val($(this.getElementsByTagName("input")[0]).attr('data-course-id'));
                                                $(".insertCourseName").text($(this.getElementsByTagName("input")[0]).attr('value'));
                                                /*close the list of autocompleted values,
                                                (or any other open lists of autocompleted values:*/
                                                closeAllLists();
                                            });
                                            a.appendChild(b);
                                        }
                                    }
                                }
                            } else {
                                console.error("Something went wrong.")
                            }
                        })
                    }
                });
                /*execute a function presses a key on the keyboard:*/
                inp.addEventListener("keydown", function(e) {
                    var x = document.getElementById(this.id + "autocomplete-list");
                    if (x) x = x.getElementsByTagName("div");
                    if (e.keyCode == 40) {
                        /*If the arrow DOWN key is pressed,
                        increase the currentFocus variable:*/
                        currentFocus++;
                        /*and and make the current item more visible:*/
                        addActive(x);
                    } else if (e.keyCode == 38) { //up
                        /*If the arrow UP key is pressed,
                        decrease the currentFocus variable:*/
                        currentFocus--;
                        /*and and make the current item more visible:*/
                        addActive(x);
                    } else if (e.keyCode == 13) {
                        /*If the ENTER key is pressed, prevent the form from being submitted,*/
                        e.preventDefault();
                        if (currentFocus > -1) {
                            /*and simulate a click on the "active" item:*/
                            if (x) x[currentFocus].click();
                        }
                    }
                });

                function addActive(x) {
                    /*a function to classify an item as "active":*/
                    if (!x) return false;
                    /*start by removing the "active" class on all items:*/
                    removeActive(x);
                    if (currentFocus >= x.length) currentFocus = 0;
                    if (currentFocus < 0) currentFocus = (x.length - 1);
                    /*add class "autocomplete-active":*/
                    x[currentFocus].classList.add("autocomplete-active");
                }

                function removeActive(x) {
                    /*a function to remove the "active" class from all autocomplete items:*/
                    for (var i = 0; i < x.length; i++) {
                        x[i].classList.remove("autocomplete-active");
                    }
                }

                function closeAllLists(elmnt) {
                    /*close all autocomplete lists in the document,
                    except the one passed as an argument:*/
                    var x = document.getElementsByClassName("autocomplete-items");
                    for (var i = 0; i < x.length; i++) {
                        if (elmnt != x[i] && elmnt != inp) {
                            x[i].parentNode.removeChild(x[i]);
                        }
                    }
                }
                /*execute a function when someone clicks in the document:*/
                document.addEventListener("click", function(e) {
                    closeAllLists(e.target);
                });
            }


            autocomplete(document.getElementById("courseSearch"));


            let $form = $('#join-course-form');
            let $alertBox = $("#join-course-error");
            let $courseIdInput = $("#course_id");
            function showError(errorMessage = null) {
                if (errorMessage == null) {
                    $alertBox.text("");
                    $alertBox.hide();
                } else {
                    $alertBox.text(errorMessage)
                    $alertBox.show();
                }
            }
            $form.submit(event => {
                event.preventDefault();
                if ($courseIdInput.val() == "" || $courseIdInput.val() == " " || $courseIdInput.val() == null || $courseIdInput.val() == undefined) {
                    showError("Please select a course in the search box below.");
                } else {
                    let course_id = $courseIdInput.val();
                    $.post("courses.php?action=join", {
                        course_id: course_id,
                    }, (data, textStatus, jqXHR) => {
                        if (textStatus != 'success' || data['error'] != undefined) {
                            if (!data['error']) {
                                showError("Something went wrong while joining the course. Please try again.");
                            } else {
                                showError("Error joining course: " + data['error']);
                            }
                        } else {
                            if (data.success == true) {
                                showError(); // hide error
                                window.location.reload();
                            } else {
                                showError("Something went wrong while joining the course. Please try again.");
                            }
                        }
                    });
                }
            })
        </script>
    <?php include('common/footer.php');
    }
} else { ?>
    Invalid Request
<?php } ?>
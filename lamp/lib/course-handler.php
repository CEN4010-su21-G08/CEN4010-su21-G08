<?php
    class CourseMembership {
        public $uid = null;
        public $ch_id = null;
        public $role = null;

        function __construct($uid, $ch_id) 
        {
            global $conn;

            $sql = "SELECT * FROM `courseMembership` WHERE `uid` = '" . $conn->real_escape_string($uid) . "' AND `ch_id` = '" . $conn->real_escape_string($ch_id) . "'";
            $result = $conn->query($sql);
            // $statement = $conn->prepare($sql);

            // $statement->bind_param("ss", $uid, $course_id);
            // $statement->execute();
            
            // $result = $statement->get_result();
            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                return null;
            }

            $courseMembership = $result->fetch_assoc();

            $this->uid = $courseMembership['uid'];
            $this->ch_id = $courseMembership['ch_id'];
            $this->role = $courseMembership['role'];
        }

        public static function is_user_member($uid, $ch_id) {
            if (new CourseMembership($uid, $ch_id) != null) {
                return true;
            } else {
                return false;
            }
        }

        public static function get_memberships($uid) 
        {
            global $conn;

            $sql = "SELECT * FROM `courseMembership` WHERE `uid` = '" . $conn->real_escape_string($uid) . "'";
            $result = $conn->query($sql);
            // $statement = $conn->prepare($sql);

            // $statement->bind_param("s", $uid);
            // $statement->execute();
            
            // $result = $statement->get_result();
            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                return array();
            }

            // $rows = $result->fetch_all(MYSQLI_ASSOC);
            $out = array();
            while ($row = $result->fetch_assoc()) {
                $out[] = $row['ch_id'];
            }

            // foreach($rows as $row) {
            //     $out[] = $row['ch_id'];
            // }

            return $out;

            
        }
        public static function get_user_courses($uid) 
        {
            global $conn;

            $sql = "SELECT * FROM `courseMembership` LEFT JOIN `courses` ON `courses`.`course_id` = `courseMembership`.`ch_id` WHERE `uid` = '" . $conn->real_escape_string($uid) . "'";
            $result = $conn->query($sql);
            // $statement = $conn->prepare($sql);

            // $statement->bind_param("s", $uid);
            // $statement->execute();
            
            // $result = $statement->get_result();
            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                return array();
            }

            // $rows = $result->fetch_all(MYSQLI_ASSOC);
            $out = array();
            while ($row = $result->fetch_assoc()) {
                $c = new CourseWithMembership();
                $c->course_code = $row['course_code'];
                $c->section_number = $row['section_number'];
                $c->instructor_email = $row['instructor_email'];
                $c->course_id = $row['course_id'];
                $c->instructor_name = $row['instructor_name'];
                $c->course_description = $row['course_description'];
                $c->course_name = $row['course_name'];
                $c->role = $row['role'];
                $out[] = $c;
            }

            return $out;
        } // includes instance of CourseWithMembership
        
        public static function create_membership($uid, $ch_id, $role = 1)
        {
            // $uid = $_SESSION['uid'];
    
            global $conn;
    
            $sql = "INSERT INTO `courseMembership` (`uid`, `ch_id`, `role`) VALUES (?, ?, ?)";
            $statement = $conn->prepare($sql);
    
            $statement->bind_param("ssi", $uid, $ch_id, $role);
            $statement->execute();
        }

        public static function get_users_in_course($course_id)
        {
            global $conn;

            $sql = "SELECT `uid` FROM `courseMembership` WHERE `courseMembership`.`ch_id` = '" . $conn->real_escape_string($course_id) . "'";

            $result = $conn->query($sql);

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                return array();
            }

            $out = array();
            while ($row = $result->fetch_assoc()) {
                $out[] = new User($row['uid']);
            }

            return $out;
        }
    };

    class CourseWithMembership extends Course {
        public $role = null;

        public function is_instructor () {
            return ($this->role == 2);
        }
    }

    class Course {
        public $course_code = null;
        public $section_number = null;
        public $instructor_email = null;
        public $course_id = null;
        public $instructor_name = null;
        public $course_description = null;
        public $course_name = null;
        

        function __construct($course_id=null) {
            if ($course_id != null) {
                global $conn;

                $sql = "SELECT * FROM `courses` WHERE `course_id` = '" . $conn->real_escape_string($course_id) . "'";
                $result = $conn->query($sql);
                // $statement = $conn->prepare($sql);

                // $statement->bind_param("s", $course_id);
                // $statement->execute();
                
                // $result = $statement->get_result();
                $numRows = mysqli_num_rows($result);
                if ($numRows <= 0) {
                    return new Course();
                }
                $course = $result->fetch_assoc();
                
                $this->course_code = $course['course_code'];
                $this->section_number = $course['section_number'];
                $this->instructor_email = $course['instructor_email'];
                $this->course_id = $course['course_id'];
                $this->instructor_name = $course['instructor_name'];
                $this->course_description = $course['course_description'];
                $this->course_name = $course['course_name'];
            }
        }
        
        public static function get_courses($course_id_array) {
            $courses = array();
            foreach($course_id_array as $course_id) {
                $c = new Course($course_id);
                $courses[$course_id] = $c;
            }

            return $courses;
        }

        public static function search_course_names($course_code = "", $section_number = "") {
            if ($course_code == "" && $section_number == "") {
                return;
            }
            if (!is_string($course_code) || !is_string($section_number)) {
                return;
            }
            $course_code = strtoupper($course_code);
            $section_number = strtoupper($section_number);

            global $conn;
            $sql = "SELECT `course_code`, `section_number`, `course_id` FROM `courses` WHERE";
            if ($course_code != "") {
                $sql .= " `course_code` LIKE '" . $conn->real_escape_string($course_code) . "%'";
            }
            if ($course_code != "" && $section_number != "") {
                $sql .= " AND";
            }
            if ($section_number != "") {
                $sql .= " `section_number` LIKE '" . $conn->real_escape_string($section_number) . "%'";
            }
            $sql .= " LIMIT 30";
            $result = $conn->query($sql);

            $results = [];
            while ($row = $result->fetch_assoc()) {
                $results[] = new CourseSearchResult($row['course_code'], $row['section_number'], $row['course_id']);
            }

            return $results;
        }

        public static function create_course() {}
        public static function update_course() {}
    };

    class CourseSearchResult {
        public $course_code = null;
        public $section_number = null;
        public $course_id = null;

        public function __construct($course_code, $section_number, $course_id) {
            $this->course_code = $course_code;
            $this->section_number = $section_number;
            $this->course_id = $course_id;
        }
    }
?>
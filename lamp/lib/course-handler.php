<?php

    class CourseMembership {
        public $uid = null;
        public $ch_id = null;
        public $type = null;

        function __construct($uid, $ch_id) 
        {
            global $conn;

            $sql = "SELECT * FROM `courseMembership` WHERE `uid` = '" . $conn->real_escape_string($uid) . "' AND `ch_id` = '" . $conn->real_escape_string($course_id) . "'";
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

            $this->$uid = courseMembership['uid'];
            $this->ch_id = courseMembership['ch_id'];
            $this->type = courseMembership['type'];
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

            $rows = $result->fetch_all(MYSQLI_ASSOC);

            $out = array();
            foreach($rows as $row) {
                $out[] = $row['ch_id'];
            }

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

            $rows = $result->fetch_all(MYSQLI_ASSOC);

            $out = array();
            foreach($rows as $row) {
                // var_dump($row);
                $c = new Course();
                $c->course_code = $row['course_code'];
                $c->section_number = $row['section_number'];
                $c->instructor_email = $row['instructor_email'];
                $c->course_id = $row['course_id'];
                $c->instructor_name = $row['instructor_name'];
                $c->course_description = $row['course_description'];
                $c->course_name = $row['course_name'];
                $out[] = $c;
            }

            return $out;
        } // includes instance of Course
        
        public static function create_membership($uid, $course_id, $role = 1)
        {
            // $uid = $_SESSION['uid'];
    
            global $conn;
    
            $sql = "INSERT INTO `courseMembership` (`uid`, `ch_id`, `role`) VALUES (?, ?, ?)";
            $statement = $conn->prepare($sql);
    
            $statement->bind_param("ssi", $uid, $ch_id, $role);
            $statement->execute();
        }
    };

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
        public static function create_course() {}
        public static function update_course() {}
    };

    
?>
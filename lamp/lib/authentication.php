<?php
    // foreach (glob("vendor/*.php") as $filename) {
    //     require_once $filename;
    // }
    // use Ramsey\Uuid\UuidInterface;
    // use Ramsey\Uuid\Uuid;
    error_reporting(-1);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    class User {
        public $uid = null;
        public $first_name = null;
        public $last_name = null;
        public $display_name = null;
        public $email = null;
        public $google_user_id = null;
        public $created_at = null;
        private $mfa_code = null;
        private $password = null;

        /* $cols should not be user-provided input */
        function __construct($uid=null, $email=null, $cols=["uid", "email", "first_name", "last_name", "display_name"]) {
            global $conn;
            if ($uid == null && $email == null) {
                // do nothing
                // values will be null
            } else {
                $sql = "SELECT ";
                if (count($cols) > 0) {
                    $first = true;
                    foreach ($cols as $colName) {
                        if (!$first) {
                            $sql .= ", ";
                        }
                        $first = false;
                        $sql .= "`" . $colName . "`";
                    }
                } else {
                    $sql .= "*";
                }

                $sql .= " FROM `users` WHERE ";

                if ($uid != null) {
                    $sql .= "`uid` = '" . $conn->real_escape_string($uid) . "'";
                } else {
                    $sql .= "`email` = '" . $conn->real_escape_string($email) . "'";
                }

                error_log($sql);

                $result = $conn->query($sql);
                $numRows = mysqli_num_rows($result);
                if ($numRows > 0) {
                    $user = $result->fetch_assoc();
                    //var_dump($user);
                    // echo "<br />";
                    //var_dump($this);
                    if (in_array('uid', $cols)) { $this->uid = $user['uid']; }
                    if (in_array('first_name', $cols)) { $this->first_name = $user['first_name']; }
                    if (in_array('last_name', $cols)) { $this->last_name = $user['last_name']; }
                    if (in_array('display_name', $cols)) { $this->display_name = $user['display_name']; }
                    if (in_array('email', $cols)) { $this->email = $user['email']; }
                    if (in_array('google_user_id', $cols)) { $this->google_user_id = $user['google_user_id']; }
                    if (in_array('created_at', $cols)) { $this->created_at = $user['created_at']; }
                    if (in_array('mfa_code', $cols)) { $this->mfa_code = $user['mfa_code']; }
                    if (in_array('password', $cols)) { $this->password = $user['password']; }
                }   
            }
        }

        public static function sign_in($email_address, $password) {
            global $conn;
            
            $u = new User(null, $email_address, ["uid", "password", "email", "first_name", "last_name", "display_name"]);
            if ($u->uid != null) {
                if (password_verify($password, $u->password)) {
                    $u->password = null;
                    $u->set_user_session();
                    return $u;
                } else {
                    return new User();
                }
            } else {
                return new User();
            }
        }

        private function set_user_session() {
            if ($this->uid != null) {
                global $conn;
                $sql = "SELECT `email`, `uid`, `first_name`, `last_name`, `display_name` FROM `users` WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";
                // $statement = $conn->prepare($sql);
                // $statement->bind_param("s", $user_uid)
                $result = $conn->query($sql);

                // $statement->execute();
                // $result = $statement->get_result();
                $user = $result->fetch_assoc();
                //echo $user['uid'];
                $_SESSION['uid'] = $this->uid;
                $_SESSION['user_id'] = $this->uid;
                $_SESSION['email'] = $this->email;
                $_SESSION['first_name'] = $this->first_name;
                $_SESSION['last_name'] = $this->last_name;
                $_SESSION['display_name'] = $this->display_name;
                $_SESSION['display_name'] = get_display_name($_SESSION['first_name'], $_SESSION['last_name'], $_SESSION['email']);
                
                global $user;
                $user = $this;
            }
        }

        public static function sign_out() {
            unset($_SESSION['uid']);
            unset($_SESSION['user_id']);
            unset($_SESSION['email']);
            unset($_SESSION['first_name']);
            unset($_SESSION['last_name']);
            unset($_SESSION['display_name']);

            global $user;
            $user = null;

            global $is_logged_in;
            $is_logged_in = false;
        }

        /* 
            Returns null if all goes well
            Returns an error message if something goes wrong
        */
        public static function create_user($first_name, $last_name, $email, $password, $verify_password, $display_name) {
            global $conn;

            /* Validate arguments:
                first name: required
                last name: required
                email: required, email address
                password: min 8 characters, max 64 chars, 3 of: 1 upper, 1 lower, 1 number, 1 symbol
                display name: any number (invalid values are already handled properly when used)
            */
            
            if (!isset($first_name) || $first_name == null || !is_string($first_name) || strlen($first_name) <= 0 || empty($first_name)) {
                return "Your first name is required.";
            }
            if (!isset($last_name) || $last_name == null || !is_string($last_name) || strlen($last_name) <= 0 || empty($last_name)) {
                return "Your last name is required.";
            }
            if (!isset($email) || $email == null || !is_string($email) || strlen($email) <= 0 || empty($email)) {
                return "Your email address is required.";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "Your email address is invalid.";
            }
            
            if (!isset($password) || $password == null || !is_string($password) || empty($password)) {
                return "Your password is required.";
            }

            if (!isset($verify_password) || $verify_password == null || !is_string($verify_password) || empty($verify_password)) {
                return "Please verify your password";
            }

            if ($password != $verify_password) {
                return "Passwords do not match";
            }

            if (strlen($password) < 8) {
                return "Your password must be at least 8 characters long.";
            }
            
            $one_upper = preg_match("/[A-Z]/", $password);
            $one_lower = preg_match("/[a-z]/", $password);
            $one_number = preg_match("/[0-9]/", $password);
            $one_symbol = preg_match("/\W/", $password);

            $cnt = 0;
            if ($one_upper) { $cnt++; }; 
            if ($one_lower) { $cnt++; }; 
            if ($one_number) { $cnt++; }; 
            if ($one_symbol) { $cnt++; }; 

            if ($cnt < 3) {
                return "Your password must contain at least three of the following: (a) one number, (b) one lowercase letter, (c) one uppercase letter, (d) one symbol. Please choose a strong password";
            }


            if (!isset($display_name) || $display_name == null || !is_integer($display_name)) {
                return "Your display name option is required.";
            }

            $sql = "INSERT INTO `users` (`uid`, `first_name`, `last_name`, `email`, `password`, `display_name`) VALUES (";
            
            $uuid = generateRandomString();
            $h_s_pass = password_hash($password, PASSWORD_DEFAULT);

            $sql .= "'" . $conn->real_escape_string($uuid) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($first_name) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($last_name) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($email) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($h_s_pass) . "'" . ", ";
            $sql .= "" . $conn->real_escape_string($display_name) . "" . "";
            $sql .= ")";

            $conn->query($sql);

            $u = new User($uuid);

            $u->set_user_session();

            header("Location: courses.php");
            
            return null;
        }

    }

    /* function sign_in_user($email_address, $password) {
        global $conn;
        $sql = "SELECT `email`, `password`, `uid` FROM `users` WHERE `email` = '" . $conn->real_escape_string($email_address) . "'";
        //var_dump($sql);
        $result = $conn->query($sql);
        
        // $statement = $conn->prepare($sql);
        // $statement->bind_param("s", $email_address);
        // $statement->execute();
        // $result = $statement->get_result();
        
        $numRows = mysqli_num_rows($result);
        if ($numRows <= 0) {
            return 0;
        }
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // good password
            set_up_user_session($user['uid']);
            return 1;
        } else {
            return 0;
        }        
    }
    */

    function does_user_have_access($uid, $channel_id) {
        global $is_logged_in;
        global $conn;
        if (!$is_logged_in) return false;
        // get channel (for type)
        $ch_type = get_channel_type($channel_id);
        if ($ch_type == 0) {
            return false; // channel not found, possibly deleted
        } else {
            $table = "";
            if ($ch_type == 1) {
                $table = "courseMembership";
            } else if ($ch_type == 2) {
                $table = "groupMembership";
            } else {
                return false;
            }
            $sql = "SELECT * FROM `". $table . "` WHERE `ch_id` = '". $conn->real_escape_string($channel_id). "' AND `uid` = '". $conn->real_escape_string($uid) . "'";
            $result = $conn->query($sql);

            // $statement = $conn->prepare($sql);

            // $statement->bind_param("ss", $channel_id, $uid);
            // $statement->execute();
            // $result = $statement->get_result();
            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                return false; // no membership entry
            } else {
                return true; 
            }
        }

    }

    /*function sign_out_user() {
        unset($_SESSION['uid']);
        unset($_SESSION['user_id']);
        unset($_SESSION['email']);
        unset($_SESSION['first_name']);
        unset($_SESSION['last_name']);
        unset($_SESSION['display_name']);
        global $is_signed_in;
        $is_signed_in = false;
    } */

    /* function set_up_user_session($user_uid) {
        global $conn;
        $sql = "SELECT `email`, `uid`, `first_name`, `last_name`, `display_name` FROM `users` WHERE `uid` = '" . $conn->real_escape_string($user_uid) . "'";
        // $statement = $conn->prepare($sql);
        // $statement->bind_param("s", $user_uid)
        $result = $conn->query($sql);

        // $statement->execute();
        // $result = $statement->get_result();
        $user = $result->fetch_assoc();
        //echo $user['uid'];
        $_SESSION['uid'] = $user['uid'];
        $_SESSION['user_id'] = $user['uid'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['display_name'] = get_display_name($_SESSION['first_name'], $_SESSION['last_name'], $_SESSION['email']);
    } */
    /* function create_user_account() {
        
        global $conn;

        $sql = "INSERT INTO `users` (`uid`, `first_name`, `last_name`, `email`, `password`, `display_name`) VALUES (?, ?, ?, ?, ?, ?)";
        $statement = $conn->prepare($sql);
        
        $first_name = parse_input('first_name', true);
        $last_name = parse_input('last_name', true);
        $email = parse_input('email', true);
        $password = parse_input('password', true);
        $display_name = intval(parse_input('display_name', true));
        
        //$_uuid = Uuid::uuid4();
        //$uuid = $_uuid->toString();
        $uuid = generateRandomString();
        
        $h_s_pass = password_hash($password, PASSWORD_DEFAULT);
        
        $statement->bind_param("sssssi", $uuid, $first_name, $last_name, $email, $h_s_pass, $display_name);
        $statement->execute();

        set_up_user_session($uuid);

        header("Location: courses.php");
    } */

    
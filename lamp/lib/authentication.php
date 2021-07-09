<?php
    // foreach (glob("vendor/*.php") as $filename) {
    //     require_once $filename;
    // }
    // use Ramsey\Uuid\UuidInterface;
    // use Ramsey\Uuid\Uuid;
    error_reporting(-1);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    function sign_in_user($email_address, $password) {
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

    function sign_out_user() {
        unset($_SESSION['uid']);
        unset($_SESSION['user_id']);
        unset($_SESSION['email']);
        unset($_SESSION['first_name']);
        unset($_SESSION['last_name']);
        unset($_SESSION['display_name']);
        global $is_signed_in;
        $is_signed_in = false;
    }

    function set_up_user_session($user_uid) {
        global $conn;
        $sql = "SELECT `email`, `uid`, `first_name`, `last_name`, `display_name` FROM `users` WHERE `uid` = '" . $conn->real_escape_string($user_uid) . "'";
        // $statement = $conn->prepare($sql);
        // $statement->bind_param("s", $user_uid)
        $result = $conn->query($sql);

        // $statement->execute();
        // $result = $statement->get_result();
        $user = $result->fetch_assoc();
        echo $user['uid'];
        $_SESSION['uid'] = $user['uid'];
        $_SESSION['user_id'] = $user['uid'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['display_name'] = $user['display_name'];
        $_SESSION['display_name'] = get_display_name($_SESSION['first_name'], $_SESSION['last_name'], $_SESSION['email']);
    }
    function create_user_account() {
        

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
    }

    
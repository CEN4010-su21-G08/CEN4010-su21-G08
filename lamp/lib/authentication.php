<?php
    // foreach (glob("vendor/*.php") as $filename) {
    //     require_once $filename;
    // }
    // use Ramsey\Uuid\UuidInterface;
    // use Ramsey\Uuid\Uuid;
    error_reporting(-1);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Not going to include Google APIs into the git repo, so:
    /*
        Download/Install
        1. Go to https://github.com/googleapis/google-api-php-client/releases
        2. Download the zip file for the PHP version being used
        3. Create the folder lamp/lib/googleapis
        4. Copy the contents of the ZIP file from #2 into the lamp/lib/googleapis folder
        5. Ensure the vendor folder is accessible at lamp/lib/googleapis/vendor. 
           If it isn't, you copied the files incorrectly
        6. Copy googleapis-config-sample.php as googleapis-config.php
        7. Fill in the appropriate values for the client id, secret, and redirect URI
    */
    require_once("lib/googleapis/vendor/autoload.php");
    require_once("lib/googleapis-config.php");

    $client = new Google\Client();
    $client->setClientId($google_client_id);
    $client->setClientSecret($google_client_secret);
    $client->setRedirectUri($google_redirect_uri);
    $client->addScope("email");
    $client->addScope("profile");
    $client->setAccessType("offline"); 
    
    /* Define User flag constants */
    // (note: defines shift)
    define("USER_ACTIVE", 0);
    define("USER_ADMIN", 1);

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
        public $flags = null;

        /* $cols should not be user-provided input */
        function __construct($uid = null, $email = null, $cols = ["uid", "email", "first_name", "last_name", "display_name", "flags", "google_user_id"]) {
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
                    if (in_array('flags', $cols)) { $this->flags = $user['flags']; }
                }   
            }
        }

        public static function sign_in($email_address, $password) {
            global $conn;
            
            $u = new User(null, $email_address, ["uid", "password", "email", "first_name", "last_name", "flags", "display_name"]);
            if ($u->uid != null) {
                if (password_verify($password, $u->password)) {
                    // check if not active
                    if (!$u->get_flag(USER_ACTIVE)) {
                        // todo: possibly include more specific error
                        return new User();
                    }
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
                $sql = "SELECT `email`, `uid`, `first_name`, `last_name`, `display_name`, `flags` FROM `users` WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";

                if (!$this->get_flag(USER_ACTIVE)) {
                    return;
                    session_destroy();
                    session_start();
                }
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
            
            if (!User::validate_string($first_name)) {
                return "Your first name is required.";
            }
            if (!User::validate_string($last_name)) {
                return "Your last name is required.";
            }
            if (!User::validate_string($email)) {
                return "Your email address is required.";
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "Your email address is invalid.";
            }
            
            if (!User::validate_string($password)) {
                return "Your password is required.";
            }

            if (!isset($verify_password) || $verify_password == null || !is_string($verify_password) || empty($verify_password)) {
                return "Please verify your password";
            }

            if ($password != $verify_password) {
                return "Passwords do not match";
            }

            if (!User::validate_password($password))
            {
                return "Your password must be at least 8 characters long and must contain at least three of the following: (a) one number, (b) one lowercase letter, (c) one uppercase letter, (d) one symbol.";
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
            $sql .= "" . (intval($display_name)) . "" . "";
            $sql .= ")";

            $conn->query($sql);

            $u = new User($uuid);

            $u->set_user_session();

            header("Location: courses.php");
            
            return null;
        }

        public function has_verified() {
            return $this->google_user_id != null;
        }

        public function create_google_sign_in_button() {
            global $client;
            $client->setLoginHint($this->email);
            ?>
                <a href="<?php echo (filter_var($client->createAuthUrl(), FILTER_SANITIZE_URL));?>" class="google-signin-button">
                    <div class="g-content-wrapper">
                        <div class="g-icon">
                            <div class="g-icon-2">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 48 48" class="abcRioButtonSvg"><g><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path><path fill="none" d="M0 0h48v48H0z"></path></g></svg>
                            </div>
                        </div>
                        <span class="g-text">Sign in with Google</span>
                    </div>
                </a>
                <br /><br />
            <?php
            }

        public function handle_google_callback($code) {
            global $client;
            if ($this->has_verified()) {
                return true;
            }
            $access_token = $client->fetchAccessTokenWithAuthCode($code);
            $client->setAccessToken($access_token);

            $profile_api = new Google_Service_PeopleService($client);
            $p = $profile_api->people->get("people/me", array('personFields' => "emailAddresses"));
            
            $emails = $p->emailAddresses;

            $hasEmail = false;
            $emailVerifiedG = false;
            $g_uid = null;

            // iterate through all emails, make sure at least one email is the user's email
            foreach ($emails as $email) {
                if ($email->value == $this->email) {
                    $hasEmail = true;
                    if ($email->metadata->verified) {
                        $emailVerifiedG = true;
                        $g_uid = $email->metadata->source->id;
                    }
                    break;
                }
            }

            if (!$hasEmail) {
                echo "Error: Your account's email is not one of the email addresses on your google account.";
            } else if (!$emailVerifiedG) {
                echo "Error: Your account's email is not a verified email address on your Google account.";
            } else if ($g_uid == null) {
                echo "Error: Something went wrong";
            } else {
                // verify email
                // by setting google_user_id to $g_uid
                global $conn;
                $sql = "UPDATE `users` SET `google_user_id`='" . $conn->real_escape_string($g_uid) . "' WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";

                $conn->query($sql);

                header("Location: verify.php?success");
            }
        } 

        public static function deactivate_account($uid)
        {
            global $conn;
            // turn off the "active" bit in the user's flags
            $sql = "UPDATE `users` SET `flags` = `flags` & ~(1 << 0) WHERE `uid` = '" . $conn->real_escape_string($uid) . "'";

            $conn->query($sql);
        }

        public static function delete_account($uid)
        {
            global $conn;

            $sql = "DELETE FROM `users` WHERE `uid` = '" . $conn->real_escape_string($uid) . "'";

            $conn->query($sql);
        }

        // sets the value of the admin flag based on the provided new value
        public function set_admin($newValue)
        {
            $this->update_flag(USER_ADMIN, $newValue);
        }

        // returns true if the user is an admin
        public function is_admin() 
        {
            return $this->get_flag(USER_ADMIN);
        }
        
        // returns true if the user is active
        public function is_active() 
        {
            return $this->get_flag(USER_ACTIVE);
        }

        // sets the value of the active flag based on the provided new value
        public function set_active($newValue)
        {
            $this->update_flag(USER_ACTIVE, $newValue);
        }

        // returns the value of a user's flag
        private function get_flag($shiftAmt) 
        {
            return $this->flags & (1 << $shiftAmt);
        }

        // updates the value of a user's flag
        private function update_flag($shiftAmt, $enabled)
        {
            global $conn;
            if (ctype_digit($shiftAmt)) {
                if ($enabled) {
                    $sql = "UPDATE `users` SET `flags` = `flags` & ~(1 << " . intval($shiftAmt) . ") WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";
                } else {
                    $sql = "UPDATE `users` SET `flags` = `flags` | (1 << " . intval($shiftAmt) . ") WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";
                }
                $conn->query($sql);
            }
        }
        public function change_displayname($display_name)
        {
            global $conn;
            if ($display_name != $this->display_name && is_integer($display_name) && !ctype_digit($display_name)){
                $display_name = intval($display_name);
                $sql = "UPDATE `users` SET `display_name` = $display_name WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";
            } else {
                //do nothing
            }
            $conn->query($sql);
        }
        
        private static function validate_password($password)
        {
            $one_upper = preg_match("/[A-Z]/", $password);
            $one_lower = preg_match("/[a-z]/", $password);
            $one_number = preg_match("/[0-9]/", $password);
            $one_symbol = preg_match("/\W/", $password);

            $cnt = 0;
            if ($one_upper) $cnt++; 
            if ($one_lower) $cnt++; 
            if ($one_number) $cnt++; 
            if ($one_symbol) $cnt++;

            if (strlen($password) >= 8 && $cnt >= 3) return true;
            else return false;
        }

        private static function validate_string($s)
        {
            if (!isset($s) || $s == null || !is_string($s) || empty($s)) {
                return false;
            }
            return true;
        }

        public function change_password($old, $new, $new_conf) {
            global $conn;

            if (!User::validate_string($new)) {
                return "New password is required.";
            }
            if (!User::validate_string($old)) {
                return "Old password is required.";
            }
            if (password_verify($old, $this->password)) {
                return "Old password is incorrect.";
            }
            if ($old == $new) {
                return "New password must be different than old password.";
            }
            if ($new == $new_conf) {
                return "Verified Password does not match.";
            }

            if (!User::validate_password($new)) {
                return "Please enter a valid password.";
            }else {
                $sql = "UPDATE `users` SET `password` = '" . $conn->real_escape_string($new) . "' WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";
            }
            $conn->query($sql);
        }

        public function change_first($new_first) {
            global $conn;

            if (!isset($new_first) || $new_first = null || !is_string($new_first) || empty($new_first)) {
                return "Please enter a first name";
            }
            if ($new_first != $this->first_name) {
                $sql = "UPDATE `users` SET `first_name` = '" . $conn->real_escape_string($new_first) . "' WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";
            }

            $conn->query($sql);
        }

        public function change_last($new_last) {
            global $conn;

            if (!isset($new_last) || $new_last = null || !is_string($new_last) || empty($new_last)) {
                return "Please enter a last name";
            }
            if ($new_last != $this->last_name) {
                $sql = "UPDATE `users` SET `last_name` = '" . $conn->real_escape_string($new_last) . "' WHERE `uid` = '" . $conn->real_escape_string($this->uid) . "'";
            }

            $conn->query($sql);
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

    function is_user_instructor($course_id)
    {
        include_once("lib/course-handler.php");
        include_once("lib/page-setup.php");
        global $user;
        
        if ($user->is_admin() == true)
        {
            return true;
        }

        $Membership = new CourseMembership($uid, $course_id);
        
        if ($Membership->role == 2)
            return true;
        else
            return false;
    }
<?php

require_once("lib/message-handler.php");
require_once("lib/punishments-handler.php");

class BasicUserInfo
{
    public $uid = null;
    public $first_name = null;
    public $last_name = null;

    function __construct($uid, $first_name, $last_name)
    {
        $this->uid = $uid;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }
}

class BasicChannelInfo
{
    public $ch_id = null;
    public $ch_name = null;
    public $ch_course_id = null;

    public $course_id = null;
    public $course_code = null;
    public $course_section_number = null;

    function __construct()
    {
    }
}

class Report
{
    public $r_id = null;
    public $reported = null;
    public $reporter = null;
    public $report_date = null;
    public $reason = null;
    public $m_id = null;
    public $ch_id = null;
    public $course_id = null;
    public $message = null;
    public $flags = null;

    // User objects
    public $reported_user = null;
    public $reporter_user = null;
    public $channel_info = null;

    function __construct($r_id = null, $reported = null, $reporter = null, $report_date = null, $reason = null, $m_id = null, $ch_id = null, $course_id = null, $message = null, $flags = null)
    {
        if ($r_id == null) {
            // do nothing
        } else if ($reported == null) {
            // query db with $r_id
            global $conn;

            $sql = "SELECT `reports`.*, `u_rr`.`first_name` AS `rr_first_name`, `u_rr`.`last_name` AS `rr_last_name`, `u_rd`.`first_name` AS `rd_first_name`, `u_rd`.`last_name` AS `rd_last_name`, `channel`.`name` AS `ch_name`, `channel`.`course_id` AS `ch_course_id`, `course`.`section_number` AS `course_section_number`, `course`.`course_code` AS `course_code` FROM `reports` LEFT JOIN `users` AS `u_rr` ON `u_rr`.`uid` = `reports`.`reporter` LEFT JOIN `users` AS `u_rd` ON `u_rd`.`uid` = `reports`.`reported` LEFT JOIN `channels` AS `channel` ON `channel`.`ch_id` = `reports`.`ch_id` LEFT JOIN `courses` AS `course` ON `course`.`course_id` = `reports`.`course_id` WHERE `reports`.`r_id` = '" . $conn->real_escape_string($r_id) . "'";


            // $sql = "SELECT * FROM `reports` WHERE `r_id` = '" . $conn->real_escape_string($r_id) .  "'";
            $result = $conn->query($sql);

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                // do nothing (no matching report)
            } else {
                $row = $result->fetch_assoc();
                
                $r = new Report();
                $this->r_id = $row['r_id'];
                $this->reported = $row['reported'];
                $this->reporter = $row['reporter'];
                $this->report_date = $row['report_date'];
                $this->reason = $row['reason'];
                $this->m_id = $row['m_id'];
                $this->ch_id = $row['ch_id'];
                $this->course_id = $row['course_id'];
                $this->message = $row['message'];
                $this->flags = $row['flags'];

                $this->reporter_user = new BasicUserInfo($row['reporter'], $row['rr_first_name'], $row['rr_last_name']);
                $this->reported_user = new BasicUserInfo($row['reported'], $row['rd_first_name'], $row['rd_last_name']);

                $this->channel_info = new BasicChannelInfo();
                $this->channel_info->ch_id = $row['ch_id'];
                $this->channel_info->ch_name = $row['ch_name'];
                $this->channel_info->ch_course_id = $row['ch_course_id'];

                $this->channel_info->course_id = $row['course_id'];
                $this->channel_info->course_code = $row['course_code'];
                $this->channel_info->course_section_number = $row['course_section_number'];
            }
        } else {
            $this->r_id = $r_id;
            $this->reported = $reported;
            $this->reporter = $reporter;
            $this->report_date = $report_date;
            $this->reason = $reason;
            $this->m_id = $m_id;
            $this->ch_id = $ch_id;
            $this->course_id = $course_id;
            $this->message = $message;
            $this->flags = $flags;
        }
    }

    public static function get($r_id)
    {
        // helper function
        return new Report($r_id);
    }

    public static function list_by_courseReports($course_id)
    {
        global $conn;
        $sql = "SELECT `reports`.*, `u_rr`.`first_name` AS `rr_first_name`, `u_rr`.`last_name` AS `rr_last_name`, `u_rd`.`first_name` AS `rd_first_name`, `u_rd`.`last_name` AS `rd_last_name`, `channel`.`name` AS `ch_name`, `channel`.`course_id` AS `ch_course_id`, `course`.`section_number` AS `course_section_number`, `course`.`course_code` AS `course_code` FROM `reports` LEFT JOIN `users` AS `u_rr` ON `u_rr`.`uid` = `reports`.`reporter` LEFT JOIN `users` AS `u_rd` ON `u_rd`.`uid` = `reports`.`reported` LEFT JOIN `channels` AS `channel` ON `channel`.`ch_id` = `reports`.`ch_id` LEFT JOIN `courses` AS `course` ON `course`.`course_id` = `reports`.`course_id` WHERE `reports`.`course_id` = '" . $conn->real_escape_string($course_id) . "'";
        $result = $conn->query($sql);
        $out = array();
        while ($row = $result->fetch_assoc()) {
            $r = new Report();
            $r->r_id = $row['r_id'];
            $r->reported = $row['reported'];
            $r->reporter = $row['reporter'];
            $r->report_date = $row['report_date'];
            $r->reason = $row['reason'];
            $r->m_id = $row['m_id'];
            $r->ch_id = $row['ch_id'];
            $r->course_id = $row['course_id'];
            $r->message = $row['message'];
            $r->flags = $row['flags'];

            $r->reporter_user = new BasicUserInfo($row['reporter'], $row['rr_first_name'], $row['rr_last_name']);
            $r->reported_user = new BasicUserInfo($row['reported'], $row['rd_first_name'], $row['rd_last_name']);

            $r->channel_info = new BasicChannelInfo();
            $r->channel_info->ch_id = $row['ch_id'];
            $r->channel_info->ch_name = $row['ch_name'];
            $r->channel_info->ch_course_id = $row['ch_course_id'];

            $r->channel_info->course_id = $row['course_id'];
            $r->channel_info->course_code = $row['course_code'];
            $r->channel_info->course_section_number = $row['course_section_number'];

            $out[] = $r;
        }
        return $out;
    }

    public static function list_by_reportedUser($user_id)
    {
        global $conn;
        $sql = "SELECT * FROM `reports` WHERE `reported` = '" . $conn->real_escape_string($user_id) . "'";
        $result = $conn->query($sql);
        $out = array();
        while ($row = $result->fetch_assoc()) {
            $out[] = new Report($row['r_id'], $row['reported'], $row['reporter'], $row['report_date'], $row['reason'], $row['m_id'], $row['ch_id'], $row['course_id'], $row['message'], $row['flags']);
        }
        return $out;
    }

    public static function list_by_reporter($user_id)
    {
        global $conn;
        $sql = "SELECT * FROM `reports` WHERE `reporter` = '" . $conn->real_escape_string($user_id) . "'";
        $result = $conn->query($sql);
        $out = array();
        while ($row = $result->fetch_assoc()) {
            $out[] = new Report($row['r_id'], $row['reported'], $row['reporter'], $row['report_date'], $row['reason'], $row['m_id'], $row['ch_id'], $row['course_id'], $row['message'], $row['flags']);
        }
        return $out;
    }

    public static function create($reason, $m_id, $ch_id)
    {
        global $conn;
        global $user;
        $reporter = $user->uid;

        $channel = new Channel($ch_id);
        $course_id = $channel->course_id;

        $m = Message::get($m_id);
        $message = $m->message;
        $reported = $m->uid;

        // a bit redundant perhaps because the channel ID should be retrieved from the database, 
        // but this should result in fewer unnecessary database requests.
        if ($m->ch_id != $ch_id) {
            return ['error' => "Invalid channel ID provided", 'status' => 400];
        }

        $r_id = generateRandomString();

        $columns = ["r_id", 'reported', 'reporter', 'm_id', 'ch_id', 'course_id', 'message', 'reason'];
        $values = [$r_id, $reported, $reporter, $m->m_id, $m->ch_id, $course_id, $message, $reason];

        $first = true;
        $sql = "INSERT INTO `reports` (";

        foreach ($columns as $col) {
            if ($first) $first = false;
            else $sql .= ", ";
            $sql .= "`" . $col . "`";
        }

        $sql .= ") VALUES (";

        $first = true;
        foreach ($values as $value) {
            if ($first) $first = false;
            else $sql .= ", ";

            $sql .= "'" . $conn->real_escape_string($value) . "'";
        }

        $sql .= ")";

        $conn->query($sql);

        return [];
    }


    /*marks report as ignored by setting 0th bit of "flags" to 1 */
    public function ignore()
    {
        global $conn;
        $sql = "UPDATE `reports` SET `flags` = flags | (1 << 0) WHERE `r_id` = '" . $conn->real_escape_string($this->r_id) . "'";
        $conn->query($sql);
    }


    /* Report Actions */
    /*deletes the message */
    public function deleteMessage()
    {
        $m = Message::get($this->m_id);

        $m->delete();

        global $conn;

        $sql = "UPDATE `reports` SET `flags` = flags | (1 << 1) WHERE `r_id` = '" . $conn->real_escape_string($this->r_id) . "'";
        $conn->query($sql);
    }
    /*mutes user */
    public function muteUser()
    {
        global $conn;
        Punishment::mute_user_in_course($this->reported, $this->course_id, $this->r_id, "");
        $sql = "UPDATE `reports` SET `flags` = flags | (1 << 2) WHERE `r_id` = '" . $conn->real_escape_string($this->r_id) . "'";
        $conn->query($sql);
    }
    /*kicks user */
    public function kickUser()
    {
        global $conn;
        Punishment::kick_user_from_course($this->reported, $this->course_id, $this->r_id, "");
        $sql = "UPDATE `reports` SET `flags` = flags | (1 << 3) WHERE `r_id` = '" . $conn->real_escape_string($this->r_id) . "'";
        $conn->query($sql);
    }
    /*bans user */
    public function banUser()
    {
        global $conn;
        Punishment::ban_user_account($this->reported, $this->course_id, $this->r_id, "");
        $sql = "UPDATE `reports` SET `flags` = flags | (1 << 4) WHERE `r_id` = '" . $conn->real_escape_string($this->r_id) . "'";
        $conn->query($sql);
    }
}

<?php
error_reporting(-1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class Message
{
    public $m_id = null;
    public $uid = null;
    public $message = null;
    public $ch_id = null;
    public $flags = null;
    public $send_date = null;
    public $edit_date = null;

    public $display_name = null;
    public $initials = null;

    function  __construct($m_id = null, $uid = null, $message = null, $ch_id = null, $flags = null, $send_date = null, $edit_date = null, $display_name = null, $initials = null)
    {
        $this->m_id = $m_id;
        $this->uid = $uid;
        $this->message = $message;
        $this->ch_id = $ch_id;
        $this->flags = $flags;
        $this->send_date = $send_date;
        $this->edit_date = $edit_date;


        $this->display_name = $display_name;
        $this->initials = $initials;
    }

    public static function send($channel_id, $uid, $message, $announcement = false)
    {
        if (!isset($channel_id)) {
            return ['error' => "Invalid channel ID provided."];
        }

        if (!does_user_have_access($uid, $channel_id)) {
            return ['error' => "Forbidden"];
        }

        if ($announcement && !is_user_instructor($channel_id)) {
            return ['error' => "Forbidden"];
        }

        global $conn;

        $sql = "INSERT INTO `messages` (`m_id`, `uid`, `message`, `ch_id`";
        if ($announcement) {
            $sql .= ", `flags`";
        }
        $sql .= ") VALUES (?, ?, ?, ?";
        if ($announcement) {
            $sql .= ", ?";
        }
        $sql .= ")";
        $statement = $conn->prepare($sql);

        $message = parse_input('message', true);
        $mid = generateRandomString();

        $uid = $_SESSION['uid'];

        $flags = intval(1);
        if ($announcement) {
            $statement->bind_param("ssssi", $mid, $uid, $message, $channel_id, $flags);
        } else {
            $statement->bind_param("ssss", $mid, $uid, $message, $channel_id);
        }

        $statement->execute();

        return ['success' => 'true'];
    }

    public static function get($message_id)
    {
        global $conn;
        global $user;
        $sql = "SELECT `messages`.*, `users`.`first_name`, `users`.`last_name`, `users`.`display_name` FROM `messages` LEFT JOIN `users` ON `messages`.`uid` = `users`.`uid` WHERE `messages`.`m_id` = '" . $conn->real_escape_string($message_id) .  "'";

        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $r = self::message_parser_helper($row);
            if (!does_user_have_access($user->uid, $r['ch_id'])) {
                return ['error' => "Forbidden"];
            }
            return new Message($r['m_id'], $r['uid'], $r['message'], $r['ch_id'], $r['flags'], $r['send_date'], $r['edit_date'], $r['display_name'], $r['initials']);
        }

        return new Message();
    }

    public static function get_many($message_ids)
    {
        $messages = [];
        foreach ($message_ids as $message_id) {
            $messages[] = self::get($message_id);
        }

        return $messages;
    }

    public static function get_after($channel_id, $after_m_id = null, $announcement = false)
    {
        global $user;
        if (!does_user_have_access($user->uid, $channel_id)) {
            return ['error' => "Forbidden"];
        }
        global $conn;
        $sql = "SELECT `messages`.*, `users`.`first_name`, `users`.`last_name`, `users`.`display_name` FROM `messages` LEFT JOIN `users` ON `messages`.`uid` = `users`.`uid` WHERE `messages`.`ch_id` = '" . $conn->real_escape_string($channel_id) . "'";
        if ($announcement) {
            $sql .= " AND (`messages`.`flags` & (1 << 0)) = 1";
        }
        if (isset($after_m_id) && $after_m_id != null) {
            $sql .= " AND `messages`.`send_date` >= ( SELECT `send_date` FROM `messages` WHERE `m_id` = '" . $conn->real_escape_string($after_m_id) . "' LIMIT 1 )";
            $sql .= " ORDER BY `messages`.`send_date` ASC LIMIT 10;";
        } else {
            $sql .= " ORDER BY `messages`.`send_date` DESC LIMIT 10;";
        }

        $result = $conn->query($sql);

        $messages = [];

        while ($row = $result->fetch_assoc()) {
            $r = self::message_parser_helper($row);
            if ($r['m_id'] != $after_m_id) { // filter out current message
                $m = new Message($r['m_id'], $r['uid'], $r['message'], $r['ch_id'], $r['flags'], $r['send_date'], $r['edit_date'], $r['display_name'], $r['initials']);
                $messages[] = $m;
            }
        }

        return $messages;
    }

    public static function get_before($channel_id, $before_m_id, $announcement = false)
    {
        global $user;
        if (!does_user_have_access($user->uid, $channel_id)) {
            return ['error' => "Forbidden"];
        }
        global $conn;
        $sql = "SELECT `messages`.*, `users`.`first_name`, `users`.`last_name`, `users`.`display_name` FROM `messages` LEFT JOIN `users` ON `messages`.`uid` = `users`.`uid` WHERE `messages`.`ch_id` = '" . $conn->real_escape_string($channel_id) . "'";
        if ($announcement) {
            $sql .= " AND (`messages`.`flags` & (1 << 0)) = 1";
        }
        $sql .= " AND `messages`.`send_date` <= ( SELECT `send_date` FROM `messages` WHERE `m_id` = '" . $conn->real_escape_string($before_m_id) . "' LIMIT 1 )";
        $sql .= " ORDER BY `messages`.`send_date` DESC LIMIT 10;";

        $result = $conn->query($sql);

        $messages = [];

        while ($row = $result->fetch_assoc()) {
            $r = self::message_parser_helper($row);
            if ($r['m_id'] != $before_m_id) { // filter out current message
                $m = new Message($r['m_id'], $r['uid'], $r['message'], $r['ch_id'], $r['flags'], $r['send_date'], $r['edit_date'], $r['display_name'], $r['initials']);
                $messages[] = $m;
            }
        }

        return $messages;
    }

    // Modifies the message object before doing anything with it
    private static function message_parser_helper($row)
    {
        if ($row['display_name'] == NULL || $row['display_name'] == NULL || $row['display_name'] == NULL) {
            $row['display_name'] = "Deleted User";
            $row["initials"] = "NA";
        } else {
            $row['display_name'] = get_display_name($row['first_name'], $row['last_name'], $row['display_name']);
            $row['initials'] = substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1);
        }
        unset($row['first_name']);
        unset($row['last_name']);
        return $row;
    }

    public function delete()
    {
        if ($this->m_id == null) {
            //no message to delete
        } else {
            global $conn;
            $sql = "DELETE FROM `messages` WHERE `m_id` = '" . $conn->real_escape_string($this->m_id) . "'";
            $conn->query($sql);
        }
    }
}

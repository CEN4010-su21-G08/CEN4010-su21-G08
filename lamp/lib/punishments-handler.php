<?php
class Punishment
{
    public $p_id = null;
    public $r_id = null;
    public $uid = null;
    public $ch_id = null;
    public $expires_at = null;
    public $reason = null;
    public $type = null;
    /*
    0 = mute
    1 = ban
    2 = kick
    3 = warn
    */
    public $course_id = null;

    function __construct($p_id = null, $r_id = null, $uid = null, $ch_id = null, $expires_at = null, $reason = null, $type = null, $course_id = null)
    {
        global $conn;

        if ($p_id - null)
        {
            // do nothing
        }
        else if ($r_id = null)
        {
            $sql = "SELECT * FROM `punishments` WHERE `p_id` = '" . $conn->real_escape_string($p_id) . "'";
            $result = $conn->query($sql);

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                // do nothing (no matching punishment)
            } else {
                $punishment = $result->fetch_assoc();

                $this->p_id = $punishment['p_id'];
                $this->r_id = $punishment['r_id'];
                $this->$uid = $punishment['uid'];
                $this->$ch_id = $punishment['ch_id'];
                $this->expires_at = $punishment['expires_at'];
                $this->reason = $punishment['reason'];
                $this->type = $punishment['type'];
                $this->course_id = $punishment['course_id'];
            }
        }
        else
        {
            $this->p_id = $p_id;
            $this->r_id = $r_id;
            $this->$uid = $uid;
            $this->$ch_id = $ch_id;
            $this->expires_at = $expires_at;
            $this->reason = $reason;
            $this->type = $type;
            $this->course_id = $course_id;
        }
    }

    private static function create($uid, $type, $course_id = null, $ch_id = null, $r_id = null, $reason = null, $expires_at = null)
    {
        global $conn;

        $p_id = generateRandomString();

        //insert into columns provided in function parameters
        $sql = "INSERT INTO `punishment` (`p_id`, ";
        if (isset($r_id)) $sql .= "`r_id`, ";
        $sql .= "`uid`, ";
        if (isset($ch_id)) $sql .= "`ch_id`, ";
        if (isset($expires_at)) $sql .= "`expires_at`, ";
        if (isset($reason)) $sql .= "`reason`, ";
        $sql .= "`type`";
        if (isset($course_id)) $sql .= ", `course_id`";

        $sql .= ") VALUES (";

        //values of columns
        $sql .= "'" . $conn->real_escape_string($p_id) . "', ";
        if (isset($r_id)) $sql .= "'" . $conn->real_escape_string($r_id) . "', ";
        $sql .= "'" . $conn->real_escape_string($uid) . "', ";
        if (isset($ch_id)) $sql .= "'" . $conn->real_escape_string($ch_id) . "', ";
        if (isset($expires_at)) $sql .= "'" . $conn->real_escape_string($expires_at) . "', ";
        if (isset($reason)) $sql .= "'" . $conn->real_escape_string($reason) . "', ";
        $sql .= "'" . $conn->real_escape_string($type) . "'";
        if (isset($course_id)) $sql .= ", '" . $conn->real_escape_string($course_id) . "'";

        $sql .= ")";

        $conn->query($sql);

        $punishment = new Punishment($p_id);

        return $punishment;
    }

    public static function ban_user_from_course($uid, $course_id, $r_id = null, $reason = null, $expires_at = null)
    {
        CourseMembership::delete_membership($uid, $course_id);
        Punishment::create($uid, 1, $course_id, null, $r_id, $reason, $expires_at);
    }

    public static function ban_user_account($uid, $r_id = null, $reason = null, $expires_at = null)
    {
        User::deactivate_account($uid);
        Punishment::create($uid, 1, null, null, $r_id, $reason, $expires_at);
    }

    public static function kick_user_from_group($uid, $ch_id, $r_id = null, $reason = null)
    {
        GroupMembership::delete_membership($uid, $ch_id);
        Punishment::create($uid, 2, null, $ch_id, $r_id, $reason, null);
    }

    public static function kick_user_from_course($uid, $course_id, $r_id = null, $reason = null)
    {
        CourseMembership::delete_membership($uid, $course_id);
        Punishment::create($uid, 2, $course_id, null, $r_id, $reason, null);
    }

    public static function kick_user_account($uid, $r_id, $reason)
    {
        User::delete_account($uid);
        Punishment::create($uid, 2, null, null, $r_id, $reason, null);
    }

    public static function mute_user_in_channel($uid, $ch_id, $r_id = null, $reason = null, $expires_at = null)
    {
        Punishment::create($uid, 0, null, $ch_id, $r_id, $reason, $expires_at);
    }

    public static function mute_user_in_course($uid, $course_id, $r_id = null, $reason = null, $expires_at = null)
    {
        Punishment::create($uid, 0, $course_id, null, $r_id, $reason, $expires_at);
    }

    public static function mute_user_account($uid, $r_id = null, $reason = null, $expires_at = null)
    {
        Punishment::create($uid, 0, null, null, $r_id, $reason, $expires_at);
    }

    public static function warn_user_in_channel($uid, $ch_id, $r_id = null, $reason = null)
    {
        Punishment::create($uid, 3, null, $ch_id, $r_id, $reason, null);
    }

    public static function warn_user_in_course($uid, $course_id, $r_id = null, $reason = null)
    {
        Punishment::create($uid, 3, $course_id, null, $r_id, $reason, null);
    }

    public static function warn_user_account($uid, $r_id = null, $reason = null)
    {
        Punishment::create($uid, 3, null, null, $r_id, $reason, null);
    }

    public static function unban_user_from_course($uid, $course_id)
    {
        global $conn;

        $sql = "DELETE FROM `punishments` WHERE `type` = '1' AND `uid` = '" . $conn->real_escape_string($uid) . "' AND `course_id` = '" . $conn->real_escape_string($course_id) . "'";

        $conn->query($sql);
    }

    public static function unban_user_account($uid)
    {
        global $conn;

        $sql = "DELETE FROM `punishments` WHERE `type` = '1' AND `course_id` = 'NULL' AND `uid` = '" . $conn->real_escape_string($uid) . "'";

        $conn->query($sql);
    }

    public static function unmute_user_in_channel($uid, $ch_id)
    {
        global $conn; 

        $sql = "DELETE FROM `punishments` WHERE `type` = '0' AND `ch_id` = '" . $conn->real_escape_string($ch_id) . "' AND `uid` = '" . $conn->real_escape_string($uid) . "'";

        $conn->query($sql);
    }

    public static function unmute_user_in_course($uid, $course_id)
    {
        global $conn; 

        $sql = "DELETE FROM `punishments` WHERE `type` = '0' AND `course_id` = '" . $conn->real_escape_string($course_id) . "' AND `uid` = '" . $conn->real_escape_string($uid) . "'";

        $conn->query($sql);
    }

    public static function unmute_user_account($uid)
    {
        global $conn; 

        $sql = "DELETE FROM `punishments` WHERE `type` = '0' AND `course_id` = 'NULL' AND `ch_id` = 'NULL' AND `uid` = '" . $conn->real_escape_string($uid) . "'";

        $conn->query($sql);
    }

    public static function is_muted($uid, $ch_id)
    {
        global $conn;

        $channel = new Channel($ch_id);

        $sql = "SELECT * FROM `punishments` WHERE `type` = '0' AND `uid` = '" . $conn->real_escape_string($uid) . "'";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc())
        {
            if (isset($row['ch_id']))
            {
                if ($row['ch_id']  == $ch_id)
                {
                    if (Punishment::is_expired($row['expires_at']))
                    {
                        $sql = "DELETE FROM `punishments` WHERE `p_id` = '" . $conn->real_escape_string($row['p_id']) . "'";
                        $conn->query($sql);
                    }
                    else
                    {
                        return true;
                    }
                }
            }
            if (isset($row['course_id']))
            {
                if ($row['course_id'] == $channel->course_id)
                {
                    if (Punishment::is_expired($row['expires_at']))
                    {
                        $sql = "DELETE FROM `punishments` WHERE `p_id` = '" . $conn->real_escape_string($row['p_id']) . "'";
                        $conn->query($sql);
                    }
                    else
                    {
                        return true;
                    }
                }
            }
            else if (!isset($row['ch_id']))
            {
                if (Punishment::is_expired($row['expires_at']))
                {
                    $sql = "DELETE FROM `punishments` WHERE `p_id` = '" . $conn->real_escape_string($row['p_id']) . "'";
                    $conn->query($sql);
                }
                else
                {
                    return true;
                }
            }
        }
        
        return false;
    }

    public static function is_banned($uid, $course_id=null)
    {
        global $conn;

        $sql = "SELECT * FROM `punishments` WHERE `course_id` = 'NULL' AND `uid` = '" . $conn->real_escape_string($uid) . "'";

        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc())
        {
            if (Punishment::is_expired($row['expires_at']))
            {
                $sql = "DELETE FROM `punishments` WHERE `p_id` = '" . $conn->real_escape_string($row['p_id']) . "'";
                $conn->query($sql);
            }
            else
            {
                return true;
            }
        }

        if (isset($course_id))
        {
            $sql = "SELECT * FROM `punishments` WHERE `course_id` = '" . $conn->real_escape_string($course_id) . "' AND `uid` = '" . $conn->real_escape_string($uid) . "'";

            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc())
            {
                if (Punishment::is_expired($row['expires_at']))
                {
                    $sql = "DELETE FROM `punishments` WHERE `p_id` = '" . $conn->real_escape_string($row['p_id']) . "'";
                    $conn->query($sql);
                }
                else
                {
                    return true;
                }
            }
        }

        return false;
    }

    public static function get_user_punishments($uid, $type = null)
    {
        global $conn;

        $out = array();

        if (isset($ype))
        {
            $sql = "SELECT * FROM `punishments` WHERE `uid` = '" . $conn->real_escape_string($uid) . "' AND `type` = '" . $conn->real_escape_string($ype) . "'";

            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc())
            {
                $punishment = new Punishment();
                $punishment->p_id = $row['p_id'];
                if (isset($row['r_id'])) $punishment->r_id = $row['r_id'];
                $punishment->$uid = $row['uid'];
                if (isset($row['ch_id'])) $punishment->ch_id = $row['ch_id'];
                if (isset($row['expires_at'])) $punishment->expires_at = $row['expires_at'];
                if (isset($row['reason'])) $punishment->reason = $row['reason'];
                if (isset($row['type'])) $punishment->type = $row['type'];
                if (isset($row['course_id'])) $punishment->course_id = $row['course_id'];

                $out[] = $punishment;
            }
        }
        else
        {
            $sql = "SELECT * FROM `punishments` WHERE `uid` = '" . $conn->real_escape_string($uid) . "'";

            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc())
            {
                $punishment = new Punishment();
                $punishment->p_id = $row['p_id'];
                if (isset($row['r_id'])) $punishment->r_id = $row['r_id'];
                $punishment->$uid = $row['uid'];
                if (isset($row['ch_id'])) $punishment->ch_id = $row['ch_id'];
                if (isset($row['expires_at'])) $punishment->expires_at = $row['expires_at'];
                if (isset($row['reason'])) $punishment->reason = $row['reason'];
                if (isset($row['type'])) $punishment->type = $row['type'];
                if (isset($row['course_id'])) $punishment->course_id = $row['course_id'];

                $out[] = $punishment;
            }
        }
        
        return $out;
    }

    public static function is_expired($expires_at)
    {
        $expire_timestamp = strtotime($expires_at);

        $current_time = time();

        $is_expired = $current_time > $expire_timestamp;

        return $is_expired;
    }
};

<?php

    class Channel
    {
        public $ch_id = null;
        public $name = null;
        public $type = null;
        public $course_id = null;

        public function __construct($ch_id, $type = null, $name = null, $course_id = null)
        {
            if ($type == null) {
                global $conn;

                $sql = "SELECT * FROM `channels` WHERE `ch_id` = '" . $conn->real_escape_string($ch_id) . "'";
                $result = $conn->query($sql);

                $numRows = mysqli_num_rows($result);
                if ($numRows <= 0) {
                    return null;
                }

                $channel = $result->fetch_assoc();

                $this->ch_id = $channel['ch_id'];
                $this->name = $channel['name'];
                $this->type = $channel['type'];
                $this->course_id = $channel['course_id'];
            } else {
                $this->ch_id = $ch_id;
                $this->name = $name;
                $this->type =$type;
                $this->course_id =$course_id;
            }
        }

        public static function get_course_channels($course_id)
        {
            global $conn;

            $sql = "SELECT * FROM `channels` WHERE `course_id` = '" . $conn->real_escape_string($course_id) . "'";
            $result = $conn->query($sql);

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                return array();
            }

            $out = array();
            while ($row = $result->fetch_assoc()) {
                $out[] = new Channel($row['ch_id'], $row['type'], $row['name'], $row['course_id']);
            }

            return $out;
        }

        public static function get_users_channels_in_course($uid, $course_id, $only_groups = false)
        {
            global $conn;

            $sql = "SELECT * FROM `groupMembership` LEFT JOIN `channels` ON `channels`.`ch_id` = `groupMembership`.`ch_id` WHERE `channels`.`course_id` = '" . $conn->real_escape_string($course_id) . "' AND `groupMembership`.`uid` = '" . $conn->real_escape_string($uid) . "'";
            $result = $conn->query($sql);

            $numRows = mysqli_num_rows($result);
            if ($numRows <= 0) {
                $out = array();
            } else {
                $out = array();
                while ($row = $result->fetch_assoc()) {
                    $out[] = new Channel($row['ch_id'], $row['type'], $row['name'], $row['course_id']);
                }
            }
            if (!$only_groups) {
                $out[] = $course_id;
            }

            return $out;
        }

        public static function create_channel($course_id, $name=null, $type=1)
        {
            global $conn;
            /*
            name: optional
            channel: default type 1
            course_id: required
            */

            if (!isset($course_id)) {
                throwError(500, "Error creating channel: course does not exist");
            }

            $ch_id = generateRandomString();

            $sql = "INSERT INTO `channels` (`ch_id`,";
            if (isset($name)) {
                $sql .= "`name`,";
            }
            $sql .= "`type`,`course_id`) VALUES (";
            $sql .= "'" . $conn->real_escape_string($ch_id) . "'" . ", ";
            if (isset($name)) {
                $sql .= "'" . $conn->real_escape_string($name) . "'" . ", ";
            }
            $sql .= "'" . $conn->real_escape_string($type) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($course_id) . "'" . ") ";

            $conn->query($sql);

            $channel = new Channel($ch_id);

            return $channel;
        }

        public static function delete_channel()
        {
        }
    }

<?php
    class Channel {
        public $ch_id = null;
        public $name = null;
        public $type = null;
        public $course_id = null;
        
        function __construct($ch_id)
        {
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
                $out[] = $row['ch_id'];
            }

            return $out;
        }

        public static function get_users_channels_in_course($uid, $course_id)
        {
            $course_channels = Channel::get_course_channels($course_id);

            $userchannels = array();

            foreach ($course_channels as $course_channel_id)
            {
                if (does_user_have_access($_SESSION['uid'], $course_channel_id))
                {
                    $userchannels[] = new Channel($course_channel_id);
                }
            }

            return $userchannels;
        }

        public static function create_channel($course_id, $name=null, $type=1)
        {
            global $conn;
            /*
            name: optional
            channel: default type 1
            course_id: required
            */

            if (!isset($course_id))
                throwError(500, "Error creating channel: course does not exist");
            
            $ch_id = generateRandomString();

            $sql .= "'" . $conn->real_escape_string($ch_id) . "'" . ", ";
            if (isset($name))
                $sql .= "'" . $conn->real_escape_string($name) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($type) . "'" . ", ";
            $sql .= "'" . $conn->real_escape_string($course_id) . "'" . ", ";

            $conn->query($sql);

            $channel = new Channel($ch_id);

            return $channel;
        }

        public static function delete_channel() {}
    }

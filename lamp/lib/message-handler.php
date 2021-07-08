<?php
    foreach (glob("vendor/*.php") as $filename) {
        require_once $filename;}

    use Ramsey\Uuid\UuidInterface;
    use Ramsey\Uuid\Uuid;
    error_reporting(-1);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    function send_message()
    {
        global $channel_id;
        if (!isset($channel_id)) {
            throwError(400, "Invalid Channel provided");
        }
        global $conn;

        $sql = "INSERT INTO `messages` (`m_id`, `uid`, `message`, `ch_id`) VALUES (?, ?, ?, ?)";
        $statement = $conn->prepare($sql);

        $message = parse_input('message', true);

        // echo '<br />';
        // echo $message;

        $_mid = Uuid::uuid4();
        $mid = $_mid->toString();

        $uid = $_SESSION['uid'];
        

        $statement->bind_param("ssss", $mid, $uid, $message, $channel_id);
        $statement->execute();
    }

    function get_messages($start_after = NULL, $json = false) {
        global $channel_id;
        global $conn;
        // $sql = "SELECT * FROM `messages` WHERE `ch_id` = ?";
        $sql = "SELECT `messages`.*, `users`.`first_name`, `users`.`last_name`, `users`.`display_name` FROM `messages` LEFT JOIN `users` ON `messages`.`uid` = `users`.`uid` WHERE `messages`.`ch_id` = ?";
        if (isset($start_after) && $start_after != NULL) {
            $sql .= " AND `messages`.`send_date` >= ?";
        }
        $sql .= " ORDER BY `messages`.`send_date` DESC LIMIT 5;";
        error_log($sql);
        error_log($start_after);
        $statement = $conn->prepare($sql);
        if (isset($start_after) && $start_after != NULL) {
            $statement->bind_param("ss", $channel_id, $start_after);
        } else {
            $statement->bind_param("s", $channel_id);
        }
        $statement->execute();
        $result = $statement->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        
        $new_rows = [];
        foreach($rows as $row) {
            if ($row['display_name'] == NULL || $row['display_name'] == NULL || $row['display_name'] == NULL) {
                $row['display_name'] = "Deleted User";
                $row["initials"] = "NA";
            } else {
                $row['display_name'] = get_display_name($row['first_name'], $row['last_name'], $row['display_name']);
                $row['initials'] = substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1);
            }
            unset($row['first_name']);
            unset($row['last_name']);
            $new_rows[] = $row;
        }

        if ($json) {
            return json_encode($new_rows);
        } else {
            return $new_rows;
        }
        // foreach($rows as $row) {
        //     var_dump($row);
            
        //     echo '<br />';
        //     echo $row["m_id"];
        //     echo '<br />';
        // }

        // // $statement->bind_result();
        // echo mysqli_num_rows($result);
    }

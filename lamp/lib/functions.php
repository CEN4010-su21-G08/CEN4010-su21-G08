<?php

function get_display_name($first_name, $last_name, $display_option) {
    switch($display_option) {
        case 1:
            return $first_name . ' ' . $last_name;
        case 2:
            return $first_name . ' ' . substr($last_name, 0, 1);
    }
}


function get_channel_type($channel_id) {
    global $conn;
    $sql = "SELECT `type` FROM `channels` WHERE `ch_id` = ?";
    $statement = $conn->prepare($sql);
    
    $statement->bind_param("s", $channel_id);
    $statement->execute();
    
    $result = $statement->get_result();
    $numRows = mysqli_num_rows($result);
    if ($numRows <= 0) {
        return 0;
    }
    $channel = $result->fetch_assoc();
    return $channel['type'];
}
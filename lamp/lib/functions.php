<?php

/* Temporary fix because composer does not work on older versions of PHP
    (the previously-used UUID package used Composer) */
/* Code Snippet taken from SO answer: https://stackoverflow.com/a/4356295 */
function generateRandomString($length = 36) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

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

function validate_input($arr, $field_name) {
    if (!isset($arr[$field_name])) {
        return false;
    }
    $data = $arr[$field_name];
    if (empty($data)) {
        return false;
    }
    $data = trim($data);
    if ($data == "") {
        return false;
    }
    return true;
}

function parse_input($field_name, $required=false) {
    if (!isset($_POST[$field_name])) {
        if ($required) throwError(400, "Invalid input. Missing field " . $field_name);
        else return NULL;
    }
    $data = $_POST[$field_name];
    if (empty($data)) {
        if ($required) throwError(400, "Invalid input. Empty field " . $field_name);
        else return NULL;
    }
    $data = trim($data);
    $data = stripslashes($data);
    // $data = htmlspecialchars($data);
    return $data;
}

function parse_get_input($field_name, $required=false) {
    if (!isset($_GET[$field_name])) {
        if ($required) throwError(400, "Invalid input. Missing field " . $field_name);
        else return NULL;
    }
    $data = $_GET[$field_name];
    if (empty($data)) {
        if ($required) throwError(400, "Invalid input. Empty field " . $field_name);
        else return NULL;
    }
    $data = trim($data);
    $data = stripslashes($data);
    // $data = htmlspecialchars($data);
    return $data;
}
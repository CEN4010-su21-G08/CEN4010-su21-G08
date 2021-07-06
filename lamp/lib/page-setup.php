<?php
    define("app_page", true);

    $is_logged_in = false;

    require_once('lib/database.php');
    require_once('lib/functions.php');
    require_once('lib/authentication.php');

    session_start();
    if (isset($_SESSION['user_id'])) {
        $is_logged_in = true;
    }

    if (isset($auth_needed) && ($auth_needed == false)) {
        
    } else {
        if (!$is_logged_in) {
            header("Location: signin.php?r");
        }
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
        $data = htmlspecialchars($data);
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
        $data = htmlspecialchars($data);
        return $data;
    }

    


<?php
include('./config/database-config.php');
function throwError($code, $message)
{
    http_response_code($code);
    die($message);
}

if (!isset($MYSQL_HOST)) {
    throwError(500, "Database Error H");
}
if (!isset($MYSQL_PORT)) throwError(500, "Database Error P");
if (!isset($MYSQL_DATABASE)) throwError(500, "Database Error D");
if (!isset($MYSQL_USERNAME)) throwError(500, "Database Error U");
if (!isset($MYSQL_PASSWORD)) throwError(500, "Database Error PS");


$conn = new mysqli($MYSQL_HOST, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE, $MYSQL_PORT);

if ($conn->connect_error) {
    throwError(500, "Database error C");
}

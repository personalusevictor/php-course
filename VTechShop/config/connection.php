<?php

include_once(dirname(__DIR__, 1) . '/helpers/errors.php');

$server = "localhost";
$username = "root";
$password = "";
$database = "vtechshop";

try {
    $conn = new PDO("mysql:host=$server;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    getError("db_failed");
    exit;
}

?>
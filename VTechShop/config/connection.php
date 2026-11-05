<?php

include_once('helpers/errors.php');

$server = "localhost";
$username = "root";
$password = "";
$database = "vtechshop";

try {
    $conn = new PDO("mysql:host=$server;dbname=$database;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    getErrors("db_failed");
}

?>
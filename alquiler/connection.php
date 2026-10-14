<?php

const DB_SERVERNAME = "localhost";
const DB_USERNAME = "root";
const DB_PASSWORD = "";
const DB_NAME = "alquiler";


try {

    $conn = new PDO("mysql:host=" . DB_SERVERNAME . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die($e->getMessage());
}

?>
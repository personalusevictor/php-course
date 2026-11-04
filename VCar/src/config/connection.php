<?php
const DB_SERVERNAME = "localhost";
const DB_USERNAME = "root";
const DB_PASSWORD = "";
const DB_NAME = "alquiler";

try {
    $conn = new PDO("mysql:host=" . DB_SERVERNAME . ";charset=utf8mb4;dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    exit('No se ha podido conectar con la base de datos.');
}

?>
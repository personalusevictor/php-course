<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "prueba";

function getEmail(PDO $conn): array {
    $users = [];

    try {
        $stmt = $conn->query("SELECT email FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    return $users;
}

function EmailValidation(PDO $conn, string $email): bool {
    $users = getEmail($conn);
    return in_array($email, $users);
}

function Connection(): PDO {
    global $servername, $username, $password, $dbname;

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        exit;
    }
}

function insertUser(PDO $conn, string $name, string $surname, string $email, string $password): void {
    try {
        if (EmailValidation($conn, $email)) {
            echo "Error: Email already exists.";
            return;
        }

        $sql = "INSERT INTO users (name, surname, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $surname, $email, $password]);
        echo "New record created successfully";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

insertUser(Connection(), "Victor", "Martin Perez", "personalusevictor@gmail.com", "1234");
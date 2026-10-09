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

function emailValidation(PDO $conn, string $email): bool {
    $users = getEmail($conn);
    return in_array($email, $users);
}

function getPassword(PDO $conn): array {
    $users = [];

    try {
        $stmt = $conn->query("SELECT password FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {
        echo $e->getMessage();
    }

    return $users;
}

function passwordValidation(PDO $conn, string $password): bool {
    $users = getPassword($conn);
    return in_array($password, $users);
}

function connection(): PDO {
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

function register(PDO $conn, string $name, string $surname, string $email, string $password): void {
    try {
        if (emailValidation($conn, $email)) {
            echo "Error: Email already exists.";
            return;
        }

        $sql = "INSERT INTO users (name, surname, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $surname, $email, password_hash($password, PASSWORD_DEFAULT)]);
        echo "New record created successfully";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function login(PDO $conn, string $email, string $password): bool {
    try {
        if(emailValidation($conn, $email) && passwordValidation($conn, $password)) {
            return false;
        }


    } catch (PDOException $th) {
        echo $th->getMessage();
    }

    return true;
}

register(connection(), "Victor", "Martin Perez", "personalusevictor@gmail.com", "1234");
<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "prueba";

function getEmail(PDO $conn): array
{
    $users = [];
    try {
        $stmt = $conn->query("SELECT email FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    return $users;
}

function emailValidation(PDO $conn, string $email): bool
{
    $users = getEmail($conn);
    return in_array($email, $users);
}

function getPassword(PDO $conn): array
{
    $users = [];
    try {
        $stmt = $conn->query("SELECT password FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    return $users;
}

function passwordValidation(PDO $conn, string $password): bool
{
    $users = getPassword($conn);
    return in_array($password, $users);
}

function connection(): PDO
{
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

function register(PDO $conn, string $name, string $surname, string $email, string $password): void
{
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
}

function login(PDO $conn, string $email, string $password): bool
{
    try {
        if (emailValidation($conn, $email) && passwordValidation($conn, $password)) {
            return false;
        }
    } catch (PDOException $th) {
        echo $th->getMessage();
    }
    echo 'Login hecho';
    return true;
}

if (isset($_POST['submit-login'])) {
    $conn = connection();
    login($conn, $_POST['email'], $_POST['password']);
}

if (isset($_POST['submit-register'])) {
    $conn = connection();
    register($conn, $_POST['name'], $_POST['surname'], $_POST['email'], $_POST['password']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register - Victor</title>
</head>

<body>
    <form method="POST">
        <h1>Registro</h1>
        <label>Nombre</label><input name="name" type="text" /><br>
        <label>Apellidos</label><input name="surname" type="text" /><br>
        <label>Email</label><input name="email" type="email" /><br>
        <label>Password</label><input name="password" type="password" /><br>
        <input type="submit" name="submit-register" />
    </form>

    <form method="POST">
        <h1>Login</h1>
        <label>Email</label><input name="email" type="email" /><br>
        <label>Password</label><input name="password" type="password" /><br>
        <input type="submit" name="submit-login" />
    </form>

    <select id="emailSelect" onchange="printEmail(this.value)">
        <option value="">Elige una opcion: </option>
        <?php foreach (getEmail(connection()) as $email) echo "<option value='$email'>$email</option>"; ?>
    </select>

    <div id="result"></div>

    <script>
        function printEmail(email) {
            if (email === "") {
                document.getElementById("result").innerHTML = "";
                return;
            }

            const xhttp = new XMLHttpRequest();

            xhttp.onload = function() {
                document.getElementById("result").innerHTML = this.responseText;
            }

            xhttp.open("GET", "procesar.php?email=" + email, true);
            xhttp.send();
        }
    </script>
</body>

</html>
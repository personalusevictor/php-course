<?php
if (isset($_GET['email'])) {
    $Selectemail = $_GET['email'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "prueba";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$Selectemail]);
    $users = $stmt->fetchAll();

    echo "<table><tr><th>Nombre</th><th>Apellidos</th><th>Email</th><th>Contraseña</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['surname']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['password']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

<?php
session_start();
require_once 'connecion.php';


function getErrors(string $codigo) {
    $errors = [
                "db_error" => "Error al establecer una conexión con la base de datos.",
                "login_invalid_credentials" => "El correo electrónico o contraseña son incorrectos.",
                "login_role" => "Acceso no autorizado. Rol de usuario no reconocido.",
            ];

    printErrors($errors[$codigo] ?? "Ha ocurrido un error desconocido.");
}

function printErrors(string $message): void { // Aquí ira el diseño de los errores con css y html avanzado...
    echo $message;
}

function login(PDO $conn, string $email_login, string $password_login) {
    $users = [];

    try {
        $stmt = $conn->prepare("SELECT id, email, password, role_id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email_login]);
        $users = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        getErrors("db_error");
    }

    if(!$users || !password_verify($password_login, $users['password'])) {
        getErrors("login_invalid_credentials");
        return;
    }

    $role_map = [1 => 'admin.php', 2 => 'index.php'];

    if(!array_key_exists($users['role_id'], $role_map)) {
        getErrors("login_role"); 
        return;
    }

    $_SESSION['user_id'] = $users['id'];
    $_SESSION['role_id'] = $users['role_id'];
    
    header("Location: " . $role_map[$users['role_id']]);
    exit();
}

function register(PDO $conn, string $dni_nie_login, string $name_login, string $surname_login, string $email_login, string $password_login, string $birth_date_login, string $driver_license_file) {
    $password_login = password_hash($password_login, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users(email, password, role_id) VALUES(:email, :password, :role_id)");
        $stmt->execute([
                ":email" => $email_login,
                ":password" => $password_login,
                ":role_id" => 2,
            ]);
        $user_id_login = $conn->lastInsertId();

        if (isset($_FILES['driver_license_file']) && $_FILES['driver_license_file']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['driver_license_file']['temp_name'];
            $unique_name_file = 'license_' . uniqid() . '.webp';
            $path = 'uploads/' . $unique_name_file;

            if (move_uploaded_file($tmp_name, $path)) {
                $stmt = $conn->prepare("INSERT INTO customers(dni_nie, name, surname, users_id, birth_date, driver_license) VALUES(:dni_nie, :name, :surname, :user_id, :birth_date, :driver_license)");
                $stmt->execute([
                    ":dni_nie" => $dni_nie_login,
                    ":name" => $name_login,
                    ":surname" => $surname_login,
                    ":user_id" => $user_id_login,
                    ":birth_date" => $birth_date_login,
                    ":driver_license" => $path,
                ]);
            }
        }
    } catch(PDOException $e) {
        getErrors("db_error");
    }


}

if(isset($_POST['submit-register'])) 
    register($conn, $_POST['dni_nie'], $_POST['name'], $_POST['surname'], $_POST['email'], $_POST['password'], $_POST['birth_date'], $_POST['driver_license_file']);

if(isset($_POST['submit-login'])) 
    login($conn, $_POST['email'], $_POST['password']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Page - Alquiler</title>
</head>

<body>
    <form method="POST">
        <h2>Inicio de Sesión</h2>
        <label>Email</label>
        <input type="email" name="email" placeholder="Introduce tu email..." />
        <label>Contraseña</label>
        <input type="password" name="password" placeholder="Introduce tu contraseña..." />
        <input type="submit" name="submit-login" />
    </form>
    <form method="POST" enctype="multipart/form-data">
        <h2>Registro</h2>
        <label>DNI/NIE</label>
        <input type="text" name="dni_nie" placeholder="Introduce tu DNI o NIE..." />
        <label>Nombre</label>
        <input type="text" name="name" placeholder="Introduce tu nombre..." />
        <label>Apellidos</label>
        <input type="text" name="surname" placeholder="Introduce tus apellidos..." />
        <label>Email</label>
        <input type="email" name="email" placeholder="Introduce tu email..." />
        <label>Contraseña</label>
        <input type="password" name="password" placeholder="Introduce tu contraseña..." />
        <label>Fecha de Nacimiento</label>
        <input type="date" name="brith_date" />
        <label>Carnet de Conducir</label>
        <input type="file" name="driver_license_file" />
        <input type="submit" name="submit-register" />
    </form>
</body>
</html>
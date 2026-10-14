<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'connection.php';

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

function getErrors(string $codigo) {
    $errors = [
                "db_error" => "Error al establecer una conexión con la base de datos.",
                "login_invalid_credentials" => "El correo electrónico o contraseña son incorrectos.",
                "login_role" => "Acceso no autorizado. Rol de usuario no reconocido.",
                "register_already_exists" => "Las credenciales introducidas ya están registradas.",
                "file_not_upload" => "El archivo no se ha subido correctamente o no lo has subido.",
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

function register(PDO $conn, string $dni_nie_register, string $name_register, string $surname_register, string $email_register, string $password_register, string $birth_date_register) {
    
    try {
        $stmt = $conn->prepare("SELECT u.email, c.dni_nie FROM users u LEFT JOIN customers c ON u.id = c.users_id WHERE u.email = :email OR c.dni_nie = :dni_nie");
        $stmt->execute([":email" => $email_register, ":dni_nie" => $dni_nie_register]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($user)) {
            getErrors("register_already_exists");
            return;
        }

    } catch (PDOException $e) {
        getErrors("db_error");
    }

    if (!isset($_FILES['driver_license_file']) || $_FILES['driver_license_file']['error'] !== UPLOAD_ERR_OK) {
        getErrors("file_not_upload");
        return;
    }
    
    $tmp_name = $_FILES['driver_license_file']['tmp_name'];
    $unique_name_file = 'license_' . uniqid() . '.webp';
    $path = 'uploads/' . $unique_name_file;

    if(!move_uploaded_file($tmp_name, $path)) {
        getErrors("file_not_upload");
        return;
    }

    $password_register = password_hash($password_register, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users(email, password, role_id) VALUES(:email, :password, :role_id)");
        $stmt->execute([
                ":email" => $email_register,
                ":password" => $password_register,
                ":role_id" => 2,
            ]);
        $user_id_register = $conn->lastInsertId();

        $stmt = $conn->prepare("INSERT INTO customers(dni_nie, name, surname, users_id, birth_date, driver_license) VALUES(:dni_nie, :name, :surname, :user_id, :birth_date, :driver_license)");
            $stmt->execute([
                ":dni_nie" => $dni_nie_register,
                ":name" => $name_register,
                ":surname" => $surname_register,
                ":user_id" => $user_id_register,
                ":birth_date" => $birth_date_register,
                ":driver_license" => $path,
            ]);
    } catch(PDOException $e) {
        getErrors("db_error");
    }
}

if(isset($_POST['submit-register'])) 
    register($conn, $_POST['dni_nie'], $_POST['name'], $_POST['surname'], $_POST['email'], $_POST['password'], $_POST['birth_date']);

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
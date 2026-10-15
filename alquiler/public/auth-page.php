<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

function login(PDO $conn, string $email_login, string $password_login){
    $users = [];

    try {
        $stmt = $conn->prepare("SELECT id, email, password, role_id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email_login]);
        $users = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
    } catch (PDOException $e) {
        getErrors("db_failed");
        return;
    }

    if(!$users || !password_verify($password_login, $users['password'])) {
        getErrors("auth_invalid_credentials");
        return;
    }

    $role_map = [1 => 'admin.php', 2 => 'index.php'];

    if(!array_key_exists($users['role_id'], $role_map)) {
        getErrors("auth_unauthorized_role");
        return;
    }

    $_SESSION['user_id'] = $users['id'];
    $_SESSION['role_id'] = $users['role_id'];

    header("Location: " . $role_map[$users['role_id']]);
    exit();
}

function register(PDO $conn, string $dni_nie_register, string $name_register, string $surname_register, string $email_register, string $password_register, string $birth_date_register){

    $birth_date_obj = DateTime::createFromFormat('Y-m-d', $birth_date_register);
    $today = new DateTime();

    if(!$birth_date_obj || $birth_date_obj->format('Y-m-d') !== $birth_date_register) {
        getErrors("validation_birth_date");
        return;
    }

    $age = $today->diff($birth_date_obj)->y;

    if($age < 18) {
        getErrors("validation_underage");
        return;
    }

    try {
        $stmt = $conn->prepare("SELECT u.email, c.dni_nie FROM users u LEFT JOIN customers c ON u.id = c.users_id WHERE u.email = :email OR c.dni_nie = :dni_nie");
        $stmt->execute([":email" => $email_register, ":dni_nie" => $dni_nie_register]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
        if (!empty($user)) {
            getErrors("auth_register_exists");
            return;
        }
    } catch (PDOException $e) {
        getErrors("db_failed");
        return;
    }

    if(!isset($_FILES['driver_license_file']) || $_FILES['driver_license_file']['error'] !== UPLOAD_ERR_OK) {
        getErrors("file_upload_failed");
        return;
    }

    $tmp_name = $_FILES['driver_license_file']['tmp_name'];

    $image_info = @getimagesize($tmp_name);
    if($image_info === false) {
        getErrors("file_invalid_image");
        return;
    }

    $mime_type = $image_info['mime'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

    if(!in_array($mime_type, $allowed_mimes)) {
        getErrors("file_invalid_image_format");
        return;
    }

    $unique_name_file = 'license_' . uniqid() . '.webp';
    $path = 'uploads/' . $unique_name_file;

    switch ($mime_type) {
        case 'image/jpeg':
            $image_resource = @imagecreatefromjpeg($tmp_name);
            break;
        case 'image/png':
            $image_resource = @imagecreatefrompng($tmp_name);
            imagepalettetotruecolor($image_resource);
            imagealphablending($image_resource, true);
            imagesavealpha($image_resource, true);
            break;
        case 'image/webp':
            $image_resource = @imagecreatefromwebp($tmp_name);
            break;
        default:
            getErrors("file_upload_failed");
            return;
    }

    if(!$image_resource) {
        getErrors("file_upload_failed");
        return;
    }

    if(!imagewebp($image_resource, $path, 80)) {
        getErrors("file_upload_failed");
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

        $stmt = null;

        $stmt = $conn->prepare("INSERT INTO customers(dni_nie, name, surname, users_id, birth_date, driver_license) VALUES(:dni_nie, :name, :surname, :user_id, :birth_date, :driver_license)");
        $stmt->execute([
            ":dni_nie" => $dni_nie_register,
            ":name" => $name_register,
            ":surname" => $surname_register,
            ":user_id" => $user_id_register,
            ":birth_date" => $birth_date_register,
            ":driver_license" => $path,
        ]);

        $stmt = null;

        $_SESSION['user_id'] = $user_id_register;
        $_SESSION['role_id'] = 2;

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        getErrors("db_failed");
        return;
    }
}

if(isset($_POST['submit-register']))
    register($conn, trim($_POST['dni_nie']), trim($_POST['name']), trim($_POST['surname']), trim($_POST['email']), $_POST['password'], $_POST['birth_date']);

if(isset($_POST['submit-login']))
    login($conn, trim($_POST['email']), $_POST['password']);
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
        <input type="email" name="email" required placeholder="Introduce tu email..." />
        <label>Contraseña</label>
        <input type="password" name="password" required placeholder="Introduce tu contraseña..." />
        <input type="submit" name="submit-login" value="Iniciar Sesión" />
    </form>

    <form method="POST" enctype="multipart/form-data">
        <h2>Registro</h2>
        <label>DNI/NIE</label>
        <input type="text" name="dni_nie" required placeholder="Introduce tu DNI o NIE..." />
        <label>Nombre</label>
        <input type="text" name="name" required placeholder="Introduce tu nombre..." />
        <label>Apellidos</label>
        <input type="text" name="surname" required placeholder="Introduce tus apellidos..." />
        <label>Email</label>
        <input type="email" name="email" required placeholder="Introduce tu email..." />
        <label>Contraseña</label>
        <input type="password" name="password" required placeholder="Introduce tu contraseña..." />
        <label>Fecha de Nacimiento</label>
        <input type="date" required name="birth_date" />
        <label>Carnet de Conducir</label>
        <input type="file" required name="driver_license_file" />
        <input type="submit" name="submit-register" value="Registrarse" />
    </form>
</body>

</html>
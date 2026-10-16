<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';

if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

function login(PDO $conn, array $login){
    $users = [];

    try {
        $stmt = $conn->prepare("SELECT id, email, password, role_id FROM users WHERE email = :email");
        $stmt->execute([':email' => $login['email']]);
        $users = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = null;
    } catch (PDOException $e) {
        getErrors("db_failed");
        return;
    }

    if(!$users || !password_verify($login['password'], $users['password'])) {
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

function register(PDO $conn, array $register){

    $birth_date_obj = DateTime::createFromFormat('Y-m-d', $register['birth_date']);
    $today = new DateTime();

    if(!$birth_date_obj || $birth_date_obj->format('Y-m-d') !== $register['birth_date']) {
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
        $stmt->execute([":email" => $register['email'], ":dni_nie" => $register['dni_nie']]);
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

    $register['password'] = password_hash($register['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users(email, password, role_id) VALUES(:email, :password, :role_id)");
        $stmt->execute([
            ":email" => $register['email'],
            ":password" => $register['password'],
            ":role_id" => 2,
        ]);
        $register_id = $conn->lastInsertId();

        $stmt = null;

        $stmt = $conn->prepare("INSERT INTO customers(dni_nie, name, surname, users_id, birth_date, driver_license) VALUES(:dni_nie, :name, :surname, :user_id, :birth_date, :driver_license)");
        $stmt->execute([
            ":dni_nie" => $register['dni_nie'],
            ":name" => $register['name'],
            ":surname" => $register['surname'],
            ":user_id" => $register_id,
            ":birth_date" => $register['birth_date'],
            ":driver_license" => $path,
        ]);

        $stmt = null;

        $_SESSION['user_id'] = $register_id;
        $_SESSION['role_id'] = 2;

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        getErrors("db_failed");
        return;
    }
}

if(isset($_POST['submit-register'])) {
    $register = [
        'dni_nie' => $_POST['dni_nie'],
        'name' => $_POST['name'],
        'surname' => $_POST['surname'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'birth_date' => $_POST['birth_date'],
    ];

    register($conn, $register);
}

if(isset($_POST['submit-login'])) {
    $login = [
            'email' => $_POST['email'],
            'password' => $_POST['password']
        ];

    login($conn, $login);
}
?>

<!DOCTYPE html>
<html lang="es">
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
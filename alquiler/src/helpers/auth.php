<?php

function login(PDO $conn, array $login){
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

    $role_map = [1 => 'index.php', 2 => 'index.php'];

    if(!array_key_exists($users['role_id'], $role_map)) {
        getErrors("auth_unauthorized_role");
        return;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $users['id'];
    $_SESSION['role_id'] = $users['role_id'];

    header("Location: " . $role_map[$users['role_id']]);
    exit();
}

function register(PDO $conn, array $register){
    if (!valid_identity($register['dni_nie']) || mb_strlen($register['name'], 'UTF-8') > 50 || mb_strlen($register['surname'], 'UTF-8') > 100 || strlen($register['email']) > 100) {
        echo 'Revisa el DNI/NIE y la longitud de tus datos.';
        return;
    }
    if (!filter_var($register['email'], FILTER_VALIDATE_EMAIL) || $register['dni_nie'] === '' || $register['name'] === '' || $register['surname'] === '' || strlen($register['password']) < 8 || strlen($register['password']) > 72) {
        echo 'Revisa tus datos. La contraseña debe tener entre 8 y 72 bytes.';
        return;
    }
    $birth_date_obj = DateTime::createFromFormat('Y-m-d', $register['birth_date']);
    $today = new DateTime();

    if(!$birth_date_obj || $birth_date_obj->format('Y-m-d') !== $register['birth_date'] || $birth_date_obj > $today) {
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

    if (!isset($_FILES['driver_license_file'])) {
        getErrors("file_upload_failed");
        return;
    }

    $license_path = upload_driver_license($_FILES['driver_license_file']);
    if ($license_path === null) {
        getErrors("file_upload_failed");
        return;
    }

    $register['password'] = password_hash($register['password'], PASSWORD_DEFAULT);

    try {
        $conn->beginTransaction();
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
            ":driver_license" => $license_path,
        ]);
        $stmt = null;

        $conn->commit();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $register_id;
        $_SESSION['role_id'] = 2;

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $stored_file = __DIR__ . '/../../storage/licenses/' . basename($license_path);
        if (is_file($stored_file)) {
            unlink($stored_file);
        }
        getErrors("db_failed");
        return;
    }
}


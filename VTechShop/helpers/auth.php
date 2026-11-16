<?php 

function register(PDO $conn, Array $register): bool {
    try {
        $stmt = $conn->prepare("INSERT INTO users(username, email, password, currency_code) VALUES (:username, :email, :password, :currency_code)");
        $stmt->execute([
            ':username' => $register['username'],
            ':email' => $register['email'],
            ':password' => $register['password'],
            ':currency_code' => $register['currency'],
        ]);
        $user_id = $conn->lastInsertId();
        $stmt = null;
    } catch (PDOException $e) {
        if ($e->getCode() === '23000' && (int)$e->errorInfo[1] === 1062) {
            $_SESSION['auth_error'] = 'user_exists';
        } else {
            $_SESSION['auth_error'] = 'db_failed';
        }
        return false;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $register['username'];

    return true;
}

function login(PDO $conn, Array $login): bool {
    try {
        $stmt = $conn->prepare("SELECT user_id, email, username, password FROM users WHERE email = :email");
        $stmt->execute([':email' => $login['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;
        
    } catch (PDOException $e) {
        $_SESSION['auth_error'] = 'db_failed';
        return false;
    }

    if (!$user || !password_verify($login['password'], $user['password'])) {
        $_SESSION['auth_error'] = 'invalid_credentials';
        return false;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];

    return true;
}

?>
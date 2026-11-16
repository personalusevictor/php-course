<?php 

function start_session_if_needed(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in(): bool {
    start_session_if_needed();
    return isset($_SESSION['user_id']);
}

function require_login(string $redirect = 'auth/auth-page.php'): void {
    if (!is_logged_in()) {
        header("Location: $redirect");
        exit;
    }
}

function register_user(PDO $conn, array $data): bool {
    start_session_if_needed();

    try {
        $stmt = $conn->prepare("INSERT INTO users(username, email, password, currency_code) VALUES (:username, :email, :password, :currency_code)");
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':currency_code' => $data['currency'] ?? 'EUR',
        ]);
        $user_id = $conn->lastInsertId();
    } catch (PDOException $e) {
        if ($e->getCode() === '23000' && (int)$e->errorInfo[1] === 1062) {
            $_SESSION['auth_error'] = 'user_exists';
        } else {
            $_SESSION['auth_error'] = 'db_failed';
        }
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$user_id;
    $_SESSION['username'] = $data['username'];
    $_SESSION['currency_code'] = $data['currency'] ?? 'EUR';
    return true;
}

function login_user(PDO $conn, array $data): bool {
    start_session_if_needed();
    try {
        $stmt = $conn->prepare("SELECT user_id, email, username, password FROM users WHERE email = :email");
        $stmt->execute([':email' => $data['email']]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $_SESSION['auth_error'] = 'db_failed';
        return false;
    }

    if (!$user || !password_verify($data['password'], $user['password'])) {
        $_SESSION['auth_error'] = 'invalid_credentials';
        return false;
    }

    $update = $conn->prepare("UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE user_id = :id");
    $update->execute([':id' => $user['user_id']]);

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['currency_code'] = $user['currency_code'] ?? 'EUR';
    return true;
}

function logout_user(): void {
    start_session_if_needed();
    session_unset();
    session_destroy();
}

function redirect(string $redirect): void {
    header('Location: auth-page.php?redirect=' . urlencode($redirect));
    exit;
}

?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once(dirname(__DIR__, 1) . '/helpers/auth.php');
include_once(dirname(__DIR__, 1) . '/helpers/currency.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: auth-page.php');
    exit;
}
    
include_once(dirname(__DIR__, 1) . '/config/connection.php');

$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

if ($action === 'register') {
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $passwordVerify = is_string($_POST['password-verify'] ?? null) ? $_POST['password-verify'] : '';
    $currency = is_string($_POST['currency'] ?? null) ? trim($_POST['currency']) : '';

    if ($username === '' || $email === '' || $password === '' || $passwordVerify === '' || $currency === '') {
        $_SESSION['auth_error'] = 'missing_fields';
        header('Location: auth-page.php');
        exit;
    }

    if (mb_strlen($username) > 50 || mb_strlen($email) > 100 || mb_strlen($password) < 8 || mb_strlen($password) > 255) {
        $_SESSION['auth_error'] = 'invalid_length';
        header('Location: auth-page.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['auth_error'] = 'invalid_email';
        header('Location: auth-page.php');
        exit;
    }

    if (!array_key_exists($currency, $coin)) {
        $_SESSION['auth_error'] = 'invalid_currency';
        header('Location: auth-page.php');
        exit;
    }

    if ($password !== $passwordVerify) {
        $_SESSION['auth_error'] = 'invalid_verify';
        header('Location: auth-page.php');
        exit;
    }

    $register = [
        "username" => $username,
        "email" => $email,
        "password" => password_hash($password, PASSWORD_DEFAULT),
        "currency" => $currency,
    ];

    if (register($conn, $register)) {
        header('Location: ../index.php');
        exit;
    }

    header('Location: auth-page.php');
    exit;

} elseif ($action === 'login') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        $_SESSION['auth_error'] = 'missing_fields';
        header('Location: auth-page.php');
        exit;
    }

    if (mb_strlen($email) > 100 || mb_strlen($password) < 8 || mb_strlen($password) > 255) {
        $_SESSION['auth_error'] = 'invalid_credentials';
        header('Location: auth-page.php');
        exit;
    }

    $login = [
        "email" => $email,
        "password" => $password,
    ];

    if (login($conn, $login)) {
        header('Location: ../index.php');
        exit;
    }

    header('Location: auth-page.php');
    exit;

} else {
    $_SESSION['auth_error'] = 'invalid_action';
    header('Location: auth-page.php');
    exit;
}

?>
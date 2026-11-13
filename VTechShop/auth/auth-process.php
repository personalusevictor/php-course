<?php
include_once(dirname(__DIR__, 1) . '/helpers/auth.php');
include_once(dirname(__DIR__, 1) . '/helpers/currency.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: auth-page.php');
    exit;
    }
    
include_once(dirname(__DIR__, 1) . '/config/connection.php');

$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

if ($action === 'register') {
    $username       = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $email          = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password       = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $passwordVerify = is_string($_POST['password-verify'] ?? null) ? $_POST['password-verify'] : '';
    $currency       = is_string($_POST['currency'] ?? null) ? trim($_POST['currency']) : '';

    if ($username === '' || $email === '' || $password === '' || $passwordVerify === '' || $currency === '') {
        getErrors('missing_fields');
        exit;
    }

    if (mb_strlen($username) > 50 || mb_strlen($email) > 100 || mb_strlen($password) < 8 || mb_strlen($password) > 255) {
        getErrors('invalid_length');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        getErrors('invalid_email');
        exit;
    }

    if (!array_key_exists($currency, $coin)) {
        getErrors('invalid_currency');
        exit;
    }

    if ($password !== $passwordVerify) {
        getErrors("invalid_verify");
        return;
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

} elseif ($action === 'login') {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        getErrors('missing_fields');
        exit;
    }

    if (mb_strlen($email) > 100 || mb_strlen($password) < 8 || mb_strlen($password) > 255) {
        getErrors('invalid_credentials');
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

} else {
    getErrors('invalid_action');
    exit;
}

?>
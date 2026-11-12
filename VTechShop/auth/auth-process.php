<?php 
include_once(dirname(__DIR__, 1) . '/config/connection.php');
include_once(dirname(__DIR__, 1) . '/helpers/auth.php');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $username       = trim($_POST['username'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $password       = $_POST['password'] ?? '';
        $passwordVerify = $_POST['password-verify'] ?? '';
        $currency       = trim($_POST['currency'] ?? '');


        if($username === '' || $email === '' || $password === '' || $passwordVerify === '' || $currency === '') {
            getErrors('missing_fields');
            exit;
        }

        if (mb_strlen($username) > 50 || mb_strlen($email) > 100 || mb_strlen($password) < 8 || mb_strlen($password) > 255) {
            getErrors('invalid_lenght');
            exit;
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            getErrors('invalid_email');
        }

        if ($password !== $passwordVerify) {
            getErrors("invalid_verify");
            return;
        }

        $register = [
            "usename" => $username,
            "email" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "currency" => $currency,
        ];

        register($conn, $register);

    } elseif ($action === 'login') {
        $email = trim($_POST['email']) ?? '';
        $password = $_POST['password'] ?? '';

        if($email === '' || $password === '') {
            getErrors('missing_fields');
            exit;
        }

        if(mb_strlen($email) > 100 || mb_strlen($password) < 8 || mb_strlen($password) > 255) {
            getErrors('invalid_credentials');
            exit;
        }

        $login = [
            "email" => $email,
            "password" => $password,
        ];
        
        $login($conn, $login);
    }
}

?>
<?php
include_once(dirname(__DIR__, 1) . '/config/connection.php');
include_once(dirname(__DIR__) . '/helpers/auth.php');
include_once(dirname(__DIR__) . '/helpers/currency.php');

start_session_if_needed();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: auth-page.php');
    exit;
}
    
$action = $_POST['action'] ?? '';
$redirect = $_POST['redirect'] ?? '';
$target_url = ($redirect === 'checkout') ? '../checkout.php' : '../index.php';

if ($action === 'logout') {
    logout_user();
    header('Location: ../index.php');
    exit;
}

if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $_SESSION['auth_error'] = 'missing_fields';
        redirect($redirect);
    }

    if (login_user($conn, ['email' => $email, 'password' => $password])) {
        header("Location: $target_url");
        exit;
    }

    redirect($redirect);
}

if ($action === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $currency = $_POST['currency'] ?? 'EUR';

    if (!$username || !$email || !$password || !$confirm) {
        $_SESSION['auth_error'] = 'missing_fields';
        redirect($redirect);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['auth_error'] = 'invalid_email';
        redirect($redirect);
    }

    if (mb_strlen($username) < 3 || mb_strlen($username) > 50 || mb_strlen($password) < 6) {
        $_SESSION['auth_error'] = 'invalid_length';
        redirect($redirect);
    }

    if ($password !== $confirm) {
        $_SESSION['auth_error'] = 'invalid_verify';
        redirect($redirect);
    }

    if (!array_key_exists($currency, get_currencies())) {
        $_SESSION['auth_error'] = 'invalid_currency';
        redirect($redirect);
    }


    $registered = register_user($conn, [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'currency' => $currency,
    ]);

    if ($registered) {
        header("Location: $target_url");
        exit;
    }

    redirect($redirect);

}

$_SESSION['auth_error'] = 'invalid_action';
header('Location: auth-page.php');
exit;

?>
<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: auth-page.php');
    exit();
}

if(isset($_POST['submit_logout_admin'])) {
    session_unset();
    session_destroy();
    header('Location: auth-page.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    Admin
    <form method="post">
        <input type="submit" name="submit_logout_admin" value="Cerrar Sesión" />
    </form>
</body>

</html>
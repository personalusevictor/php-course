<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(dirname(__DIR__) . '/helpers/currency.php');
require_once(dirname(__DIR__) . '/helpers/errors.php');

$auth_error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Autenticación - VTechShop</title>
</head>
<body>
    <?php if (isset($auth_error)): ?>
        <?php getErrors($auth_error); ?>
    <?php endif; ?>
    <form method="POST" action="auth-process.php" autocomplete="on">
        <input type="hidden" name="action" value="register">
        <label for="register-username">Nombre de usuario</label><input type="text" id="register-username" name="username" autocomplete="username" maxlength="50" required />
        <label for="register-email">Email</label><input type="email" id="register-email" name="email" autocomplete="email" maxlength="100" required />
        <label for="register-password">Contraseña</label><input type="password" id="register-password" name="password" autocomplete="new-password" minlength="8" maxlength="255" required />
        <label for="register-password-verify">Verificar Contraseña</label><input type="password" id="register-password-verify" name="password-verify" minlength="8" maxlength="255" autocomplete="new-password" required />
        <label for="register-currency">Moneda predeterminada</label>
        <select id="register-currency" name="currency" required>
            <?php foreach ($coin as $code => $symbol): ?>
                <option value="<?= htmlspecialchars($code) ?>" <?= ($code === 'EUR') ? 'selected' : '' ?> ><?= htmlspecialchars($code) ?> (<?= htmlspecialchars($symbol) ?>)</option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Registrarse" />
    </form>
    <form method="POST" action="auth-process.php">
        <input type="hidden" name="action" value="login">
        <label for="login-email">Email</label><input type="email" id="login-email" name="email" autocomplete="email" maxlength="100" required />
        <label for="login-password">Contraseña</label><input type="password" id="login-password" name="password" autocomplete="current-password" minlength="8" maxlength="255" required />
        <input type="submit" value="Inicia Sesión" />
    </form>
</body>
</html>
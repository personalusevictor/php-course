<?php
session_start();
require_once __DIR__ . '/../src/config/connection.php';
require_once __DIR__ . '/../src/helpers/errors.php';
require_once __DIR__ . '/../src/helpers/upload.php';
require_once __DIR__ . '/../src/helpers/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../src/helpers/auth.php';

ob_start();

if (isset($_POST['submit-register'])) {
    $register = [
        'dni_nie' => strtoupper(input('dni_nie')),
        'name' => input('name'),
        'surname' => input('surname'),
        'email' => input('email'),
        'password' => (is_string($_POST['password'] ?? null) ? $_POST['password'] : ''),
        'birth_date' => input('birth_date'),
    ];

    register($conn, $register);
}

if (isset($_POST['submit-login'])) {
    $login = [
        'email' => input('email'),
        'password' => (is_string($_POST['password'] ?? null) ? $_POST['password'] : '')
    ];

    login($conn, $login);
}
$form_error = ob_get_clean();
$register_mode = isset($_POST['submit-register']) || (($_GET['mode'] ?? '') === 'register');
require_once __DIR__ . '/../src/views/layout.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu cuenta · VCar</title>
    <link rel="stylesheet" href="assets/app.css?v=vcar-footer-color-4">
    <link rel="icon" type="image/png" href="assets/vcar-memoji.png">
    <meta name="theme-color" content="#f5ece3">
</head>

<body class="auth-body">
    <?php site_header('auth'); ?>
    <main id="main" class="page auth-page <?= $register_mode ? 'auth-register' : 'auth-login' ?>">
        <div class="auth-intro"><span class="auth-kicker"><?= icon('lock-keyhole') ?>TU CUENTA VCAR</span>
            <h1><?= $register_mode ? 'Crea tu cuenta' : 'Bienvenido de nuevo' ?></h1>
            <p><?= $register_mode ? 'Regístrate para reservar y gestionar tus alquileres.' : 'Inicia sesión para consultar disponibilidad y gestionar tus reservas.' ?></p>
        </div>
        <nav class="auth-switch" aria-label="Acceso a tu cuenta"><a href="auth-page.php" <?= !$register_mode ? 'aria-current="page"' : '' ?>>Iniciar sesión</a><a href="auth-page.php?mode=register" <?= $register_mode ? 'aria-current="page"' : '' ?>>Crear cuenta</a></nav>
        <?php if ($form_error): ?><p role="alert"><?= $form_error ?></p><?php endif; ?>
        <?php if (!$register_mode): ?>
            <section class="auth-panel surface">
                <h2>Iniciar sesión</h2>
                <p>Para clientes y administradores.</p>
                <form method="post"><?= csrf_field() ?>
                    <div class="field"><label for="login-email"><?= icon('mail') ?>Email</label><input id="login-email" type="email" name="email" autocomplete="username" value="<?= htmlspecialchars(input('email')) ?>" required placeholder="tu@email.com"></div>
                    <div class="field"><label for="login-password"><?= icon('lock-keyhole') ?>Contraseña</label><input id="login-password" type="password" name="password" autocomplete="current-password" required placeholder="Tu contraseña"></div>
                    <button name="submit-login"><?= icon('log-in') ?>Iniciar sesión</button>
                </form>
            </section>
            <p class="auth-alternative">¿Todavía no tienes cuenta? <a href="auth-page.php?mode=register">Regístrate</a></p>
        <?php else: ?>
            <section class="auth-panel surface">
                <h2>Crear cuenta</h2>
                <p>Completa tus datos para poder reservar.</p>
                <form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
                    <div class="field-row">
                        <div class="field"><label for="register-name">Nombre</label><input id="register-name" type="text" name="name" maxlength="50" autocomplete="given-name" value="<?= htmlspecialchars(input('name')) ?>" required placeholder="Tu nombre"></div>
                        <div class="field"><label for="register-surname">Apellidos</label><input id="register-surname" type="text" name="surname" maxlength="100" autocomplete="family-name" value="<?= htmlspecialchars(input('surname')) ?>" required placeholder="Tus apellidos"></div>
                    </div>
                    <div class="field"><label for="register-email"><?= icon('mail') ?>Email</label><input id="register-email" type="email" name="email" maxlength="100" autocomplete="email" value="<?= htmlspecialchars(input('email')) ?>" required placeholder="tu@email.com"></div>
                    <div class="field-row">
                        <div class="field"><label for="register-dni"><?= icon('id-card') ?>DNI / NIE</label><input id="register-dni" type="text" name="dni_nie" value="<?= htmlspecialchars(input('dni_nie')) ?>" required placeholder="12345678Z"></div>
                        <div class="field"><label for="register-birth">Fecha de nacimiento</label><input id="register-birth" type="date" name="birth_date" autocomplete="bday" value="<?= htmlspecialchars(input('birth_date')) ?>" required></div>
                    </div>
                    <div class="field"><label for="register-password"><?= icon('lock-keyhole') ?>Contraseña</label><input id="register-password" type="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" required placeholder="Al menos 8 caracteres" aria-describedby="password-help"><span class="help" id="password-help">Usa al menos 8 caracteres. Evita contraseñas fáciles de adivinar.</span></div>
                    <div class="field"><label for="register-license"><?= icon('upload') ?>Foto del carnet de conducir</label><input id="register-license" type="file" name="driver_license_file" accept=".jpg,.jpeg,.png,.webp" required aria-describedby="license-help"><span class="help" id="license-help">JPG, PNG o WebP. Máximo 5 MB y 5000 × 5000 píxeles. Solo tú y el administrador podéis acceder a la foto. Si hay un error, vuelve a seleccionarla.</span></div>
                    <button name="submit-register"><?= icon('user-plus') ?>Crear mi cuenta</button>
                </form>
            </section>
            <p class="auth-alternative">¿Ya tienes cuenta? <a href="auth-page.php">Inicia sesión</a></p>
        <?php endif; ?>
    </main><?php site_footer(); ?>
</body>
</html>
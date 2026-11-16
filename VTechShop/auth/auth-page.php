<?php
require_once(dirname(__DIR__) . '/helpers/auth.php');
require_once(dirname(__DIR__) . '/helpers/currency.php');
require_once(dirname(__DIR__) . '/helpers/errors.php');
require_once(dirname(__DIR__) . '/views/layout.php');

start_session_if_needed();
if(is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);
$redirect = $_GET['redirect'] ?? '';
$currencies = get_currencies();

render_head('Página de Autenticación - VTechShop', '../');
render_header('../');
?>

<main>
    <?php render_error($error); ?>
    <section>
        <h2>Iniciar Sesión</h2>
        <form action="auth-process.php" method="post">
            <input type="hidden" name="action" value="login" />
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div>
                <label for="login-email">Correo electrónico</label><input type="email" id="login-email" name="email" required />
            </div>
            <div>
                <label for="login-password">Contraseña</label><input type="password" id="login-password" name="password" required />
            </div>
            <input type="submit" value="Entrar">
        </form>
    </section>
    <section>
        <h2>Crear Cuenta</h2>
        <form action="auth-process.php" method="post">
            <input type="hidden" name="action" value="register" />
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>" />
            <div>
                <label for="reg-username">Nombre de usuario</label><input type="text" id="reg-username" name="username" minlength="3" maxlength="50" required />
            </div>
            <div>
                <label for="reg-email">Correo electrónico</label><input type="email" id="reg-email" name="email" required />
            </div>
            <div>
                <label for="reg-password">Contraseña</label><input type="password" id="reg-password" name="password" minlength="6" required />
            </div>
            <div>
                <label for="reg-confirm">Repetir contraseña</label><input type="password" id="reg-confirm" name="confirm_password" minlength="6" required />
            </div>
            <div>
                <label for="reg-confirm">Moneda preferida</label>
                <select id="reg-currency" name="currency">
                    <?php foreach ($currencies as $code => $symbol): ?>
                        <option value="<?= $code ?>" <?= $code === 'EUR' ? 'selected' : '' ?>> <?= $code ?>(<?= $symbol ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="Registrarse">
            </div>
        </form>
    </section>
</main>

<?php render_footer('../') ?>
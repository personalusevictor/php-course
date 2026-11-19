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

$initial_tab = in_array($error, ['user_exists', 'invalid_verify', 'invalid_currency', 'invalid_length', 'invalid_email'], true) ? 'register' : 'login';
render_head('Tu cuenta · VTechShop', '../', 'page-auth');
render_header('../');
?>
<main id="main-content" class="site-main">
    <div class="auth-layout">
        <div class="auth-form-panel" data-auth-tabs data-initial-tab="<?= $initial_tab ?>">
            <div class="auth-tabs" role="tablist" aria-label="Acceso a tu cuenta" hidden><button type="button" role="tab" id="tab-login" aria-controls="panel-login" data-tab="login">Iniciar sesión</button><button type="button" role="tab" id="tab-register" aria-controls="panel-register" data-tab="register">Crear cuenta</button></div>
            <?php render_error($error); ?>
            <section id="panel-login" aria-labelledby="tab-login">
                <h2>Qué bueno verte de nuevo.</h2><p class="form-intro">Entra en tu cuenta y continúa con tu equipo.</p>
                <form action="auth-process.php" method="post"><input type="hidden" name="action" value="login"><input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <div class="form-field"><label for="login-email">Correo electrónico</label><input type="email" id="login-email" name="email" autocomplete="email" required></div>
                    <div class="form-field"><label for="login-password">Contraseña</label><div class="password-wrap"><input type="password" id="login-password" name="password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password="login-password" aria-label="Mostrar contraseña">Mostrar</button></div></div>
                    <button type="submit" class="button button-accent">Entrar en mi cuenta</button>
                </form>
            </section>
            <section id="panel-register" aria-labelledby="tab-register">
                <h2>Tu próximo montaje empieza aquí.</h2><p class="form-intro">Crea tu cuenta para guardar tus pedidos.</p>
                <form action="auth-process.php" method="post"><input type="hidden" name="action" value="register"><input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <div class="form-field"><label for="reg-username">Nombre de usuario</label><input type="text" id="reg-username" name="username" autocomplete="username" minlength="3" maxlength="50" required></div>
                    <div class="form-field"><label for="reg-email">Correo electrónico</label><input type="email" id="reg-email" name="email" autocomplete="email" required></div>
                    <div class="form-row"><div class="form-field"><label for="reg-password">Contraseña</label><input type="password" id="reg-password" name="password" autocomplete="new-password" minlength="6" required><small>Al menos 6 caracteres.</small></div><div class="form-field"><label for="reg-confirm">Repetir contraseña</label><input type="password" id="reg-confirm" name="confirm_password" autocomplete="new-password" minlength="6" required></div></div>
                    <div class="form-field"><label for="reg-currency">Moneda preferida</label><select id="reg-currency" name="currency"><?php foreach ($currencies as $code => $symbol): ?><option value="<?= $code ?>" <?= $code === 'EUR' ? 'selected' : '' ?>><?= $code ?> (<?= $symbol ?>)</option><?php endforeach; ?></select></div>
                    <button type="submit" class="button button-accent">Crear mi cuenta</button>
                </form>
            </section>
        </div>
    </div>
</main>
<?php render_footer('../'); ?>

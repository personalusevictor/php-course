<?php

declare(strict_types=1);
session_start();

const STARTING_MONEY = 1000.00;
const STATE_COOKIE = 'ruleta_state_v4';
function cookieSecret(): string {
    return hash('sha256', 'ruleta-europea-clase-demo-v6|' . PHP_VERSION);
}

function money(float $amount): string {
    return number_format($amount, 2, ',', '.') . ' €';
}

function saveStateCookie(): void {
    $payload = json_encode([
        'dinero' => round((float)$_SESSION['dinero'], 2),
        'historial' => array_slice($_SESSION['historial'] ?? [], -50),
        'movimientos' => array_slice($_SESSION['movimientos'] ?? [], -50),
    ], JSON_UNESCAPED_UNICODE);

    $encoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', $encoded, cookieSecret());
    
    setcookie(STATE_COOKIE, $encoded . '.' . $signature, ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly' => true, 'samesite' => 'Lax']);
}

function restoreStateCookie(): void {
    if (empty($_COOKIE[STATE_COOKIE])) return;
    [$encoded, $signature] = array_pad(explode('.', $_COOKIE[STATE_COOKIE], 2), 2, '');
    if (!$encoded || !$signature || !hash_equals(hash_hmac('sha256', $encoded, cookieSecret()), $signature)) return;
    $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);
    if ($decoded === false) return;
    $data = json_decode($decoded, true);
    if (!is_array($data)) return;
    if (!isset($_SESSION['dinero']) || $_SESSION['dinero'] === STARTING_MONEY) if (isset($data['dinero']) && is_numeric($data['dinero'])) $_SESSION['dinero'] = round((float)$data['dinero'], 2);
    if (empty($_SESSION['historial']) && isset($data['historial']) && is_array($data['historial'])) $_SESSION['historial'] = array_slice($data['historial'], -50);
    if (empty($_SESSION['movimientos']) && isset($data['movimientos']) && is_array($data['movimientos'])) $_SESSION['movimientos'] = array_slice($data['movimientos'], -50);
}

$_SESSION['dinero'] ??= STARTING_MONEY;
$_SESSION['historial'] ??= [];
$_SESSION['apuestasPendientes'] ??= [];
$_SESSION['movimientos'] ??= [];
restoreStateCookie();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['accion'] ?? '';
    $amount = round((float)($_POST['cantidad'] ?? 0), 2);
    $type = $_POST['tipo'] ?? '';

    if (!in_array($type, ['ingreso', 'retirada'], true)) $error = 'Selecciona si quieres ingresar o retirar dinero.';
    elseif ($amount < 1 || $amount > 100000) $error = 'Introduce una cantidad entre 1 € y 100.000 €.';
    elseif (empty($_FILES['certificado']) || $_FILES['certificado']['error'] !== UPLOAD_ERR_OK) $error = 'Necesitas adjuntar un certificado bancario en PDF.';
    elseif ($_FILES['certificado']['size'] > 5 * 1024 * 1024) $error = 'El PDF no puede superar 5 MB.';
    
    else {
        $tmp = $_FILES['certificado']['tmp_name'];
        $name = $_FILES['certificado']['name'];
        $isPdf = strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'pdf' && file_get_contents($tmp, false, null, 0, 4) === '%PDF';

        if (!$isPdf) $error = 'El certificado debe ser un PDF válido.';
        elseif ($type === 'retirada' && (float)$_SESSION['dinero'] < $amount) $error = 'No tienes saldo disponible suficiente para retirar esa cantidad.';
        else {
            $dir = __DIR__ . '/uploads';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $stored = 'cert-' . bin2hex(random_bytes(8)) . '.pdf';
            move_uploaded_file($tmp, $dir . '/' . $stored);
            if ($type === 'ingreso') $_SESSION['dinero'] = round((float)$_SESSION['dinero'] + $amount, 2);
            else $_SESSION['dinero'] = round((float)$_SESSION['dinero'] - $amount, 2);
            $_SESSION['movimientos'][] = ['tipo' => $type, 'cantidad' => $amount, 'certificado' => $name, 'fecha' => date('d/m/Y H:i:s'), 'saldoFinal' => (float)$_SESSION['dinero']];
            $_SESSION['movimientos'] = array_slice($_SESSION['movimientos'], -50);
            saveStateCookie();
            $message = $type === 'ingreso' ? 'Ingreso realizado.' : 'Retirada realizada.';
        }
    }
}

$movimientos = array_reverse($_SESSION['movimientos']);
function movimientoLabel(string $tipo): string {
    return $tipo === 'ingreso' ? 'Ingreso' : 'Retirada';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#f5f5f7">
    <title>Dinero · Ruleta Europea</title>
    <link rel="stylesheet" href="assets/css/ruleta.css">
</head>

<body>
    <?php include_once 'includes/header.php'; ?>
    <main class="app finance-page">
        <section class="finance-hero glass">
            <div><small>GESTIÓN DE DINERO</small>
                <h1>Ingresar o retirar dinero</h1>
                <p>Cada movimiento requiere adjuntar un certificado bancario en PDF.</p>
            </div>
            <div class="finance-balance">
                <span>Saldo disponible</span><strong><?= money((float)$_SESSION['dinero']) ?></strong>
                <a href="index.php" class="return-ruleta">Volver a la ruleta</a>
            </div>
        </section>

        <?php if ($message): ?>
            <div class="finance-alert success">
                <?= htmlspecialchars($message) ?></div><?php endif; ?><?php if ($error): ?><div class="finance-alert error"><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <section class="finance-grid">
            <form class="finance-card glass" method="post" enctype="multipart/form-data"><input type="hidden" name="accion" value="movimiento">
                <div class="finance-tabs">
                    <label><input type="radio" name="tipo" value="ingreso" checked><span>Ingresar</span></label>
                    <label><input type="radio" name="tipo" value="retirada"><span>Retirar</span></label></div><label class="field"><span>Cantidad</span>
                    <input name="cantidad" type="number" min="1" max="100000" step="0.01" placeholder="50,00" required></label><label class="upload"><span>Certificado del banco (PDF)</span>
                    <input name="certificado" type="file" accept="application/pdf,.pdf" required><small>PDF de ejemplo · máximo 5 MB</small></label>
                    <button class="primary" type="submit">Continuar</button>
            </form>
            <section class="finance-card glass">
                <div class="history-head">
                    <div><small>MOVIMIENTOS</small>
                        <h2>Ingresos y retiradas</h2>
                    </div><span><?= count($movimientos) ?></span>
                </div>
                <div class="movement-list"><?php if (!$movimientos): ?><p>No hay movimientos todavía.</p><?php else: foreach (array_slice($movimientos, 0, 20) as $m): ?>
                    <article>
                        <div class="movement-icon <?= $m['tipo'] === 'ingreso' ? 'in' : 'out' ?>"><?= $m['tipo'] === 'ingreso' ? '↓' : '↑' ?></div>
                                <div>
                                    <b><?= movimientoLabel($m['tipo']) ?></b><small><?= money((float)$m['cantidad']) ?> · <?= htmlspecialchars((string)$m['fecha']) ?></small>
                                    <small>Certificado: <?= htmlspecialchars((string)$m['certificado']) ?></small>
                                </div>
                                <strong class="<?= $m['tipo'] === 'ingreso' ? 'positive' : 'negative' ?>"><?= $m['tipo'] === 'ingreso' ? '+' : '-' ?><?= money((float)$m['cantidad']) ?></strong>
                    </article><?php endforeach; endif; ?>
                </div>
            </section>
        </section>
    </main>

    <?php include_once 'includes/footer.php'; ?>
</body>
</html>
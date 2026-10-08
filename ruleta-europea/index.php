<?php

declare(strict_types=1);
session_start();

const STARTING_MONEY = 1000.00;
const STATE_COOKIE = 'ruleta_state_v4';
const EURO_WHEEL = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
const RED_NUMBERS = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

function color(int $number): string {
    return $number === 0 ? 'verde' : (in_array($number, RED_NUMBERS, true) ? 'rojo' : 'negro');
}

function paridad(int $number): ?string {
    return $number === 0 ? null : ($number % 2 === 0 ? 'par' : 'impar');
}

function docena(int $number): ?string {
    return $number < 1 ? null : ($number <= 12 ? 'primera' : ($number <= 24 ? 'segunda' : ($number <= 36 ? 'tercera' : null)));
}

function altoBajo(int $number): ?string {
    return $number < 1 ? null : ($number <= 18 ? 'bajo' : ($number <= 36 ? 'alto' : null));
}

function multiplier(string $type): int {
    return match ($type) {
        'numero' => 36,
        'color', 'paridad', 'altoBajo' => 2,
        'docena' => 3,
        default => 0
    };
}

function wins(array $bet, int $number): bool {
    return match ($bet['tipo']) {
        'numero' => (int)$bet['eleccion'] === $number,
        'color' => $bet['eleccion'] === color($number),
        'paridad' => $bet['eleccion'] === paridad($number),
        'docena' => $bet['eleccion'] === docena($number),
        'altoBajo' => $bet['eleccion'] === altoBajo($number),
        default => false,
    };
}

function money(float $amount): string {
    return number_format($amount, 2, ',', '.') . ' €';
}

function wheelIndex(int $number): int {
    return array_search($number, EURO_WHEEL, true);
}

function betKey(string $type, string|int $choice): string {
    return $type . ':' . $choice;
}

function currentStake(): float {
    return round(array_sum(array_column($_SESSION['apuestasPendientes'], 'cantidad')), 2);
}

function validBet(string $type, mixed $choice): bool {
    return match ($type) {
        'numero' => is_numeric($choice) && (int)$choice >= 0 && (int)$choice <= 36,
        'color' => in_array($choice, ['rojo', 'negro'], true),
        'paridad' => in_array($choice, ['par', 'impar'], true),
        'docena' => in_array($choice, ['primera', 'segunda', 'tercera'], true),
        'altoBajo' => in_array($choice, ['bajo', 'alto'], true),
        default => false,
    };
}

function cookieSecret(): string {
    return hash('sha256', 'ruleta-europea|' . PHP_VERSION);
}

function saveStateCookie(): void {
    $payload = json_encode([
        'dinero' => round((float)$_SESSION['dinero'], 2),
        'historial' => array_slice($_SESSION['historial'], -50),
        'movimientos' => array_slice($_SESSION['movimientos'] ?? [], -50),
    ], JSON_UNESCAPED_UNICODE);
    $encoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', $encoded, cookieSecret());
    setcookie(STATE_COOKIE, $encoded . '.' . $signature, [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function restoreStateCookie(): void {
    if (empty($_COOKIE[STATE_COOKIE])) return;
    [$encoded, $signature] = array_pad(explode('.', $_COOKIE[STATE_COOKIE], 2), 2, '');
    if (!$encoded || !$signature || !hash_equals(hash_hmac('sha256', $encoded, cookieSecret()), $signature)) return;
    $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);
    if ($decoded === false) return;
    $data = json_decode($decoded, true);
    if (!is_array($data)) return;
    if (!isset($_SESSION['dinero']) || $_SESSION['dinero'] === STARTING_MONEY) {
        if (isset($data['dinero']) && is_numeric($data['dinero'])) $_SESSION['dinero'] = round((float)$data['dinero'], 2);
    }
    if (empty($_SESSION['historial']) && isset($data['historial']) && is_array($data['historial'])) $_SESSION['historial'] = array_slice($data['historial'], -50);
    if (empty($_SESSION['movimientos']) && isset($data['movimientos']) && is_array($data['movimientos'])) $_SESSION['movimientos'] = array_slice($data['movimientos'], -50);
}

$_SESSION['dinero'] ??= STARTING_MONEY;
$_SESSION['historial'] ??= [];
$_SESSION['apuestasPendientes'] ??= [];
$_SESSION['movimientos'] ??= [];
restoreStateCookie();

function state(?string $message = null, string $type = 'info', ?array $round = null): never {
    saveStateCookie();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => $type !== 'error',
        'mensaje' => $message,
        'tipoMensaje' => $type,
        'dinero' => round((float)$_SESSION['dinero'], 2),
        'saldoPendiente' => currentStake(),
        'totalCapital' => round((float)$_SESSION['dinero'] + currentStake(), 2),
        'apuestasPendientes' => array_values($_SESSION['apuestasPendientes']),
        'historial' => array_slice(array_reverse($_SESSION['historial']), 0, 20),
        'movimientos' => array_slice(array_reverse($_SESSION['movimientos']), 0, 20),
        'ronda' => $round,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['accion'] ?? '';

    if ($action === 'apostar') {
        $type = (string)($_POST['tipo'] ?? '');
        $choice = $_POST['eleccion'] ?? '';
        $mode = $_POST['modo'] ?? 'add';
        $amount = round((float)($_POST['cantidad'] ?? 0), 2);
        if (!validBet($type, $choice)) state('Apuesta no válida.', 'error');
        if ($amount < 1 || $amount > 100000) state('La ficha debe ser de al menos 1 €.', 'error');
        $choice = $type === 'numero' ? (int)$choice : $choice;
        $key = betKey($type, $choice);
        $found = null;
        foreach ($_SESSION['apuestasPendientes'] as $index => $bet) {
            if (betKey($bet['tipo'], $bet['eleccion']) === $key) {
                $found = $index;
                break;
            }
        }

        if ($mode === 'add') {
            if ((float)$_SESSION['dinero'] < $amount) state('No tienes saldo suficiente.', 'error');
            $_SESSION['dinero'] = round((float)$_SESSION['dinero'] - $amount, 2);
            if ($found === null) $_SESSION['apuestasPendientes'][] = ['tipo' => $type, 'eleccion' => $choice, 'cantidad' => $amount];
            else $_SESSION['apuestasPendientes'][$found]['cantidad'] = round((float)$_SESSION['apuestasPendientes'][$found]['cantidad'] + $amount, 2);
            state('Apuesta colocada.', 'exito');
        }
        if ($mode === 'remove') {
            if ($found === null) state('No hay una apuesta en esa casilla.', 'error');
            $betAmount = (float)$_SESSION['apuestasPendientes'][$found]['cantidad'];
            $refund = min($amount, $betAmount);
            $remaining = round($betAmount - $refund, 2);
            $_SESSION['dinero'] = round((float)$_SESSION['dinero'] + $refund, 2);
            if ($remaining <= 0) unset($_SESSION['apuestasPendientes'][$found]);
            else $_SESSION['apuestasPendientes'][$found]['cantidad'] = $remaining;
            $_SESSION['apuestasPendientes'] = array_values($_SESSION['apuestasPendientes']);
            state('Apuesta reducida.', 'info');
        }
        state('Operación no válida.', 'error');
    }

    if ($action === 'vaciar') {
        $refund = currentStake();
        $_SESSION['dinero'] = round((float)$_SESSION['dinero'] + $refund, 2);
        $_SESSION['apuestasPendientes'] = [];
        state('Todas las apuestas han sido retiradas.', 'info');
    }

    if ($action === 'jugar') {
        $number = random_int(0, 36);
        $pending = $_SESSION['apuestasPendientes'];
        $stake = currentStake();
        $return = 0.0;
        $detail = [];
        foreach ($pending as $bet) {
            $won = wins($bet, $number);
            $prize = $won ? round((float)$bet['cantidad'] * multiplier($bet['tipo']), 2) : 0.0;
            $return += $prize;
            $detail[] = [...$bet, 'ganada' => $won, 'premio' => $prize];
        }
        $_SESSION['dinero'] = round((float)$_SESSION['dinero'] + $return, 2);
        $net = round($return - $stake, 2);
        $round = [
            'numero' => $number,
            'color' => color($number),
            'paridad' => paridad($number),
            'docena' => docena($number),
            'altoBajo' => altoBajo($number),
            'apuestas' => $detail,
            'totalApostado' => round($stake, 2),
            'ganancia' => round($return, 2),
            'resultado' => $net,
            'saldoFinal' => round((float)$_SESSION['dinero'], 2),
            'wheelIndex' => wheelIndex($number),
            'fecha' => date('d/m/Y H:i:s')
        ];
        $_SESSION['historial'][] = $round;
        $_SESSION['historial'] = array_slice($_SESSION['historial'], -50);
        $_SESSION['apuestasPendientes'] = [];
        state($stake > 0 ? 'La ruleta ha girado y las apuestas han sido liquidadas.' : 'Giro sin apuesta registrado.', 'info', $round);
    }

    if ($action === 'reiniciar') {
        $_SESSION['dinero'] = STARTING_MONEY;
        $_SESSION['historial'] = [];
        $_SESSION['movimientos'] = [];
        $_SESSION['apuestasPendientes'] = [];
        saveStateCookie();
        state('Partida reiniciada.', 'info');
    }
    state('Acción no válida.', 'error');
}

function labelBet(array $bet): string {
    $labels = ['rojo' => 'Rojo', 'negro' => 'Negro', 'par' => 'Par', 'impar' => 'Impar', 'primera' => '1ª docena', 'segunda' => '2ª docena', 'tercera' => '3ª docena', 'bajo' => '1–18', 'alto' => '19–36'];
    return $bet['tipo'] === 'numero' ? 'Número ' . $bet['eleccion'] : ($labels[$bet['eleccion']] ?? (string)$bet['eleccion']);
}

$dinero = (float)$_SESSION['dinero'];
$pending = $_SESSION['apuestasPendientes'];
$history = array_reverse($_SESSION['historial']);
$last = $history[0] ?? null;

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#f5f5f7">
    <title>Ruleta Europea</title>
    <link rel="stylesheet" href="assets/css/ruleta.css">
</head>
<body class="<?= $last ? 'theme-' . $last['color'] : '' ?>">
    <?php include_once 'includes/header.php'; ?>
    <main class="app">
        <section class="top">
            <div class="roulette glass">
                <div class="title">
                    <div><small>EUROPEAN ROULETTE</small>
                        <h1>Ruleta europea</h1>
                    </div>
                    <div class="last">Último resultado <b id="lastResult"><?= $last ? $last['numero'] . ' · ' . ucfirst($last['color']) : '—' ?></b></div>
                </div>
                <div class="wheel-stage">
                    <div class="wheel-frame">
                        <div class="pointer" aria-hidden="true"><span></span></div>
                        <div class="wheel" id="wheel"><svg viewBox="0 0 420 420" aria-label="Ruleta europea">
                                <defs>
                                    <radialGradient id="wood" cx="35%" cy="30%">
                                        <stop offset="0" stop-color="#d7ad6d" />
                                        <stop offset=".35" stop-color="#9c6930" />
                                        <stop offset="1" stop-color="#4e2f12" />
                                    </radialGradient>
                                    <radialGradient id="metal">
                                        <stop offset="0" stop-color="#8b8d91" />
                                        <stop offset=".35" stop-color="#27292c" />
                                        <stop offset="1" stop-color="#0b0c0e" />
                                    </radialGradient>
                                    <filter id="shadow">
                                        <feDropShadow dx="0" dy="10" stdDeviation="10" flood-opacity=".42" />
                                    </filter>
                                </defs>
                                <circle cx="210" cy="210" r="205" fill="#090a0b" />
                                <circle cx="210" cy="210" r="199" fill="url(#wood)" />
                                <circle cx="210" cy="210" r="190" fill="#0e0f11" />
                                <circle cx="210" cy="210" r="184" fill="#18191c" stroke="#c39550" stroke-width="5" />
                                <g id="wheelRotator"></g>
                                <circle cx="210" cy="210" r="67" fill="url(#metal)" stroke="#a9783b" stroke-width="5" />
                                <circle cx="210" cy="210" r="57" fill="#111215" stroke="#292b2e" stroke-width="7" />
                                <g class="hub-text"><text x="210" y="199" text-anchor="middle">EUROPEAN</text><text x="210" y="228" text-anchor="middle" id="wheelNumber"><?= $last ? $last['numero'] : '0' ?></text><text x="210" y="244" text-anchor="middle">ROULETTE</text></g>
                            </svg></div>
                    </div>
                </div>
                <div class="result">
                    <div id="resultBall" class="ball <?= $last ? 'ball--' . $last['color'] : '' ?>"><?= $last ? $last['numero'] : '—' ?></div>
                    <div><small>Resultado</small><b id="resultText"><?= $last ? ucfirst($last['color']) . ' · ' . $last['numero'] : 'Listo para girar' ?></b></div>
                    <strong id="resultNet" class="<?= $last && $last['resultado'] > 0 ? 'positive' : ($last && $last['resultado'] < 0 ? 'negative' : '') ?>"><?= $last && $last['totalApostado'] > 0 ? (($last['resultado'] > 0 ? '+' : '') . money((float)$last['resultado'])) : 'Sin apuesta' ?></strong>
                </div>
            </div>
            <aside class="money glass"><small>DINERO</small><label>Saldo disponible</label><strong id="balance"><?= money($dinero) ?></strong>
                <hr>
                <div><span>En mesa</span><b id="pendingTotal"><?= money(currentStake()) ?></b></div>
                <div class="money-note"><span>Capital total</span><b id="totalMoney"><?= money($dinero + currentStake()) ?></b></div>
                <button id="spin" class="primary">Girar ruleta</button><button id="clear" class="secondary">Retirar todas</button>
                <a class="money-link" href="finanzas.php">Ingresar / retirar dinero</a>
            </aside>
        </section>

        <section class="bets glass">
            <div class="bet-head">
                <div><small>MESA DE APUESTAS</small>
                    <h2>Elige una ficha y juega directamente</h2>
                </div>
                <div class="chips">
                    <button class="chip active" data-chip="1">1 €</button>
                    <button class="chip" data-chip="5">5 €</button><button class="chip" data-chip="25">25 €</button>
                    <button class="chip" data-chip="100">100 €</button>
                    <div class="custom">
                        <input id="custom" type="number" min="1" max="100000" step="0.01" inputmode="decimal" placeholder="Cantidad">
                        <button id="useCustom">Aplicar</button>
                    </div>
                </div>
            </div>
            <div class="toolbar">
                <span>Ficha seleccionada <b id="selected">1,00 €</b></span>
                <span>Click para sumar · click derecho para quitar</span>
                <b id="tableTotal"><?= money(currentStake()) ?></b>
            </div>
            <div class="table" id="table">
                <button class="cell zero <?= array_filter($pending, fn($b) => $b['tipo'] === 'numero' && $b['eleccion'] === 0) ? 'draft' : '' ?>" data-type="numero" data-choice="0">0</button>
                <div class="numbers">
                    <?php 
                        for ($n = 1; $n <= 36; $n++): $has = array_filter($pending, fn($b) => $b['tipo'] === 'numero' && $b['eleccion'] === $n); 
                    ?>
                    <button class="cell <?= color($n) === 'rojo' ? 'red' : 'black' ?> <?= $has ? 'draft' : '' ?>" data-type="numero" data-choice="<?= $n ?>"><?= $n?>
                    </button>
                    <?php endfor; ?>
                </div>
                <div class="outside">
                    <div class="outside-row outside-row--dozens">
                        <?php
                        $outs1 = [['docena', 'primera', '1ª DOCENA', '1–12 · 2:1'], ['docena', 'segunda', '2ª DOCENA', '13–24 · 2:1'], ['docena', 'tercera', '3ª DOCENA', '25–36 · 2:1']];
                        foreach ($outs1 as $o): $has = array_filter($pending, fn($b) => $b['tipo'] === $o[0] && $b['eleccion'] === $o[1]); ?>
                        <button class="cell out <?= $has ? 'draft' : '' ?>" data-type="<?= $o[0] ?>" data-choice="<?= $o[1] ?>">
                            <b><?= $o[2] ?></b><small><?= $o[3] ?></small>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="outside-row outside-row--even">
                        <?php
                        $outs2 = [['altoBajo', 'bajo', '1–18', '1:1'], ['paridad', 'par', 'PAR', '1:1'], ['color', 'rojo', 'ROJO', '1:1'], ['color', 'negro', 'NEGRO', '1:1'], ['paridad', 'impar', 'IMPAR', '1:1'], ['altoBajo', 'alto', '19–36', '1:1']];
                        foreach ($outs2 as $o): $has = array_filter($pending, fn($b) => $b['tipo'] === $o[0] && $b['eleccion'] === $o[1]); ?>
                        <button class="cell out <?= $o[2] === 'ROJO' ? 'red' : ($o[2] === 'NEGRO' ? 'black' : '') ?> <?= $has ? 'draft' : '' ?>" data-type="<?= $o[0] ?>" data-choice="<?= $o[1] ?>">
                            <b><?= $o[2] ?></b><small><?= $o[3] ?></small>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="bet-footer">
                <div id="current" class="current"><em><?= $pending ? count($pending) . ' apuestas en mesa' : 'Sin apuestas en mesa' ?></em></div>
                <span class="bet-tip">El giro confirma y liquida todo lo que esté sobre la mesa.</span>
            </div>
        </section>

        <section class="history glass">
            <div class="history-head">
                <div><small>HISTORIAL</small>
                    <h2>Resultados recientes</h2>
                </div><span id="count"><?= count($history) ?></span>
            </div>
            <div id="historyList" class="history-list"><?php if (!$history): ?>
                <p>Los giros aparecerán aquí, aunque no hayas apostado.</p>
                <?php else: foreach (array_slice($history, 0, 20) as $h): ?>
                    <article class="history-card">
                        <div class="hball <?= $h['color'] ?>"><?= $h['numero'] ?></div>
                            <div class="history-main">
                                <b><?= ucfirst($h['color']) ?></b>
                                <small>
                                    <?= $h['fecha'] ?> · <?= $h['totalApostado'] > 0 ? money((float)$h['totalApostado']) . ' apostados' : 'Sin apuesta' ?>
                                </small>
                                <?php if (!empty($h['apuestas'])): ?>
                                    <div class="history-bets"><?php foreach ($h['apuestas'] as $bet): ?>
                                        <span class="<?= !empty($bet['ganada']) ? 'won' : 'lost' ?>">
                                            <?= htmlspecialchars(labelBet($bet), ENT_QUOTES, 'UTF-8') ?> · <?= money((float)$bet['cantidad']) ?>
                                            <?= !empty($bet['ganada']) ? ' · +' . money((float)$bet['premio']) : '' ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <strong class="<?= $h['totalApostado'] === 0 ? 'muted' : ($h['resultado'] >= 0 ? 'positive' : 'negative') ?>">
                                <?= $h['totalApostado'] === 0 ? '—' : (($h['resultado'] >= 0 ? '+' : '') . money((float)$h['resultado'])) ?>
                            </strong>
                    </article><?php endforeach; endif; ?>
            </div>
        </section>
        <div id="toast" class="toast" hidden></div>
    </main>
    
    <?php include_once 'includes/footer.php'; ?>

    <script>
        window.__INITIAL_BETS__ = <?= json_encode(array_values($pending), JSON_UNESCAPED_UNICODE) ?>;
        window.__INITIAL_LAST__ = <?= json_encode($last, JSON_UNESCAPED_UNICODE) 
    ?>;
    </script>
    <script src="assets/js/ruleta.js"></script>
</body>
</html>
<?php

session_start();

//Si las varaibles cookies no estan en la sesion se crean
if(!isset($_SESSION['dinero'])) $_SESSION['dinero'] = 1000.00;
if(!isset($_SESSION['historial'])) $_SESSION['historial'] = [];
if(!isset($_SESSION['apuestasPendientes'])) $_SESSION['apuestasPendientes'] = [];

//Hacemos esto para poder utilizar más comodamente las variables de sesion
$dinero = $_SESSION['dinero'];
$historial = $_SESSION['historial'];
$apuestasPendientes = $_SESSION['apuestasPendientes'];

$ronda = null;
$mensaje = null;
$tipoMensaje = null;

//Funcion - Obtener Color
function obtenerColor(int $numero): string {
    $rojos = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

    if ($numero === 0) return 'verde';

    return in_array($numero, $rojos, true) ? 'rojo' : 'negro';
}

//Funcion - Obtener Paridad
function obtenerParidad(int $numero): ?string {
    if ($numero === 0) return null;

    return $numero % 2 === 0 ? 'par' : 'impar';
}

//Funcion - Obtener Docena
function obtenerDocena(int $numero): ?string {
    if ($numero >= 1 && $numero <= 12) return 'primera';
    if ($numero >= 13 && $numero <= 24) return 'segunda';
    if ($numero >= 25 && $numero <= 36) return 'tercera';

    return null;
}

//Funcion - Obtener Alto y Bajo
function obtenerAltoBajo(int $numero): ?string {
    if ($numero >= 1 && $numero <= 18) return 'bajo';
    if ($numero >= 19 && $numero <= 36) return 'alto';

    return null;
}

//Funcion - Saber si un número es valido o no
function numeroValido(int $numero): bool {
    return $numero >= 0 && $numero <= 36;
}

//Funcion - Saber si la cantidad introducida es valida o no
function cantidadValida(float $cantidad, float $dinero): bool {
    return $cantidad >= 1 && $cantidad <= $dinero;
}

//Funcion - Crea la apuesta
function crearApuesta(string $tipo, mixed $eleccion, float $cantidad): array {
    return ['tipo' => $tipo, 'eleccion' => $eleccion, 'cantidad' => round($cantidad, 2)]; //Devuelve un array con el tipo de apuesta, la elección y la cantidad de dinero introducida redondeada a dos decimales
}

//Funcion - Comprueba la apuesta, la compara con el resultado ganador contra la apuesta hecha.
function comprobarApuesta(array $apuesta, int $numeroGanador): bool {
    switch ($apuesta['tipo']) {
        case 'numero':
            return $apuesta['eleccion'] === $numeroGanador;

        case 'color':
            return $apuesta['eleccion'] === obtenerColor($numeroGanador);

        case 'paridad':
            return $apuesta['eleccion'] === obtenerParidad($numeroGanador);

        case 'docena':
            return $apuesta['eleccion'] === obtenerDocena($numeroGanador);

        case 'altoBajo':
            return $apuesta['eleccion'] === obtenerAltoBajo($numeroGanador);

        default:
            return false;
    }
}

//Funcion - Obtiene cuanto vale cada tipo de apuesta
function obtenerMultiplicador(string $tipo): int {
    switch ($tipo) {
        case 'numero':
            return 36;

        case 'color':
            return 2;

        case 'paridad':
            return 2;

        case 'docena':
            return 3;

        case 'altoBajo':
            return 2;

        default:
            return 0;
    }
}

//Funcion - Pone en funcionamiento la ruleta
function jugarRonda(array $apuestas, float $dinero): array {
    $numeroGanador = random_int(0, 36);
    $colorGanador = obtenerColor($numeroGanador);
    $paridadGanadora = obtenerParidad($numeroGanador);
    $docenaGanadora = obtenerDocena($numeroGanador);
    $altoBajoGanador = obtenerAltoBajo($numeroGanador);
    $gananciaRonda = 0;
    $apuestasResultado = [];

    $totalApostado = 0;
    foreach ($apuestas as $apuesta) {
        $ganada = comprobarApuesta($apuesta, $numeroGanador);
        $premio = 0;
        $totalApostado += $apuesta['cantidad'];

        if ($ganada) {
            $multiplicador = obtenerMultiplicador($apuesta['tipo']);
            $premio = $apuesta['cantidad'] * $multiplicador;
            $dinero += $premio;
            $gananciaRonda += $premio;
        }

        $apuesta['ganada'] = $ganada;
        $apuesta['premio'] = $premio;
        $apuestasResultado[] = $apuesta;
    }

    $resultadoRonda = $gananciaRonda - $totalApostado;

    $ronda = [
        'numero' => $numeroGanador,
        'color' => $colorGanador,
        'paridad' => $paridadGanadora,
        'docena' => $docenaGanadora,
        'altoBajo' => $altoBajoGanador,
        'apuestas' => $apuestasResultado,
        'totalApostado' => $totalApostado,
        'ganancia' => $gananciaRonda,
        'resultado' => $resultadoRonda
    ];

    return [
        'error' => false,
        'numero' => $numeroGanador,
        'color' => $colorGanador,
        'paridad' => $paridadGanadora,
        'docena' => $docenaGanadora,
        'altoBajo' => $altoBajoGanador,
        'apuestas' => $apuestasResultado,
        'ganancia' => $gananciaRonda,
        'resultado' => $resultadoRonda,
        'dinero' => $dinero,
        'ronda' => $ronda
    ];
}

//Girar Ruleta - Procesando peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    //Preparas la apuesta 
    if (isset($_POST['tipo'])) {
        $tipo = $_POST['tipo'];
        $eleccion = $_POST['eleccion'] ?? null;
        $cantidad = $_POST['cantidad'] ?? null;

        //Comprobación de cantidad
        if (!is_numeric($cantidad)) {
            $mensaje = 'La cantidad introducida no es válida.';
            $tipoMensaje = 'error';
        } else {
            $cantidad = (float) $cantidad;

            //Comprobamos que la cantidad es válida
            if ($cantidad < 1) {
                $mensaje = 'La apuesta mínima es de 1 €.';
                $tipoMensaje = 'error';
            } else {

                //Comprobamos el tipo de apuesta
                $tiposValidos = [
                    'numero',
                    'color',
                    'paridad',
                    'docena',
                    'altoBajo'
                ];

                if (!in_array($tipo, $tiposValidos, true)) {
                    $mensaje = 'El tipo de apuesta no es válido.';
                    $tipoMensaje = 'error';
                } else {

                    //Comprobamos la elección escogida
                    $eleccionValida = true;
                    switch ($tipo) {
                        case 'numero':
                            if (!is_numeric($eleccion) || (int)$eleccion < 0 || (int)$eleccion > 36) $eleccionValida = false;
                            else  $eleccion = (int)$eleccion;
                            break;

                        case 'color':
                            if (!in_array($eleccion, ['rojo', 'negro'], true)) $eleccionValida = false;
                            break;

                        case 'paridad':
                            if (!in_array($eleccion, ['par', 'impar'], true)) $eleccionValida = false;
                            break;

                        case 'docena':
                            if (!in_array($eleccion, ['primera', 'segunda', 'tercera'], true)) $eleccionValida = false;
                            break;

                        case 'altoBajo':
                            if (!in_array($eleccion, ['alto', 'bajo'], true)) $eleccionValida = false;
                            break;
                    }

                    if (!$eleccionValida) {
                        $mensaje = 'La opción seleccionada no es válida.';
                        $tipoMensaje = 'error';
                    } else {

                        //Calculamos el total de apuestas pendientes
                        $totalPendiente = 0;

                        foreach ($apuestasPendientes as $apuesta) {
                            $totalPendiente += $apuesta['cantidad'];
                        }

                        $nuevoTotal = $totalPendiente + $cantidad;
                        
                        //Comprobamos el saldo disponible
                        if ($nuevoTotal > $dinero) {
                            $mensaje = 'No puedes apostar más dinero del que tienes disponible.';
                            $tipoMensaje = 'error';
                        } else {

                            //Creamos la apuesta
                            $nuevaApuesta = crearApuesta(
                                $tipo,
                                $eleccion,
                                $cantidad
                            );

                            $dinero -= $cantidad;

                            $apuestasPendientes[] = $nuevaApuesta;

                            //Guardamos todo esto en _SESSION
                            $_SESSION['dinero'] = $dinero;
                            $_SESSION['apuestasPendientes'] = $apuestasPendientes;
                            $mensaje = 'Apuesta preparada correctamente.';
                            $tipoMensaje = 'exito';
                        }
                    }
                }
            }
        }
    }
}

    //Giramos la ruleta
    if (isset($_POST['accion']) && $_POST['accion'] === 'jugar') {
        $resultado = jugarRonda($apuestasPendientes, $dinero);
        if ($resultado['error']) {
            $mensaje = $resultado['mensaje'];
            $tipoMensaje = 'error';
        } else {

            $dinero = $resultado['dinero'];
            $ronda = $resultado['ronda'];

            $historial[] = $ronda;
            $apuestasPendientes = [];

            $_SESSION['dinero'] = $dinero;
            $_SESSION['historial'] = $historial;
            $_SESSION['apuestasPendientes'] = $apuestasPendientes;

            $mensaje = 'La ruleta ha girado.';
            $tipoMensaje = 'info';
        }
    }

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruleta Europea</title>
    <link rel="stylesheet" href="ruleta.css">
</head>

<body>
    <?php include_once 'includes/header.php'; ?>
    <?php if ($mensaje !== null): ?>
    <div class="mensaje <?php echo $tipoMensaje; ?>">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
    <?php endif; ?>
    <main>
        <section class="zona-ruleta">
            <h2>Ruleta</h2>
            <div class="ruleta">

                <!--
                    Aquí ira la ruleta europea después
                -->

                <div class="ruleta-centro">
                    <span>
                        <?php
                        if ($ronda !== null) {
                            echo $ronda['numero'];
                        } else {
                            echo '-';
                        }
                        ?>
                    </span>
                </div>
            </div>
            <div class="resultado">
                <h3>Resultado</h3>
                <div class="numero-resultado">
                    <?php
                    if ($ronda !== null) {
                        echo $ronda['numero'];
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
                <p>
                    <?php
                    if ($ronda !== null) {
                        echo ucfirst($ronda['color']);
                    }
                    ?>
                </p>
            </div>
        </section>
        <section class="panel-apuestas">
            <h2>Realizar apuesta</h2>
            <div class="tipo-apuesta">
                <h3>Número</h3>
                <form method="POST">
                    <input type="hidden" name="tipo"value="numero">
                    <label for="numero">Número:</label>
                    <select id="numero" name="eleccion" required>
                        <?php for ($i = 0; $i <= 36; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <label for="cantidad-numero">Cantidad:</label>
                    <input type="number" id="cantidad-numero" name="cantidad" min="1" step="0.01" required>
                    <button type="submit"> Apostar </button>
                </form>
            </div>
            <div class="tipo-apuesta">
                <h3>Color</h3>
                <form method="POST">
                    <input type="hidden" name="tipo" value="color">
                    <label><input type="radio" name="eleccion" value="rojo" required>Rojo</label>
                    <label><input type="radio" name="eleccion" value="negro">Negro</label>
                    <label for="cantidad-color">Cantidad:</label>
                    <input type="number" id="cantidad-color" name="cantidad" min="1" step="0.01" required>
                    <button type="submit">Apostar</button>
                </form>
            </div>
            <div class="tipo-apuesta">
                <h3>Par / Impar</h3>
                <form method="POST">
                    <input type="hidden" name="tipo" value="paridad">
                    <label><input type="radio" name="eleccion" value="par" required>Par</label>
                    <label><input type="radio" name="eleccion" value="impar">Impar</label>
                    <label for="cantidad-paridad">Cantidad:</label>
                    <input type="number" id="cantidad-paridad" name="cantidad" min="1" step="0.01" required>
                    <button type="submit">Apostar</button>
                </form>
            </div>

            <div class="tipo-apuesta">
                <h3>Docena</h3>
                <form method="POST">
                    <input type="hidden" name="tipo" value="docena">
                    <label><input type="radio" name="eleccion" value="primera" required>1ª (1-12)</label>
                    <label><input type="radio" name="eleccion" value="segunda">2ª (13-24)</label>
                    <label><input type="radio" name="eleccion" value="tercera">3ª (25-36)</label>
                    <label for="cantidad-docena">Cantidad:</label>
                    <input type="number" id="cantidad-docena" name="cantidad" min="1" step="0.01" required>
                    <button type="submit">Apostar</button>

                </form>

            </div>

            <div class="tipo-apuesta">
                <h3>Alto / Bajo</h3>
                <form method="POST">
                    <input type="hidden" name="tipo" value="altoBajo">
                    <label><input type="radio" name="eleccion" value="bajo" required>Bajo (1-18)</label>
                    <label><input type="radio" name="eleccion" value="alto">Alto (19-36)</label>
                    <label for="cantidad-alto-bajo">Cantidad:</label>
                    <input type="number" id="cantidad-alto-bajo" name="cantidad" min="1" step="0.01" required>
                    <button type="submit">Apostar</button>
                </form>
            </div>
        </section>
    </main>

    <section class="apuestas-pendientes">
        <h2>Apuestas preparadas</h2>
        <?php if (empty($apuestasPendientes)): ?>
            <p>No tienes apuestas preparadas.</p>
        <?php else: ?>
            <div class="lista-apuestas">
                <?php foreach ($apuestasPendientes as $apuesta): ?>
                    <div class="apuesta">
                        <span class="apuesta-tipo"> <?php echo ucfirst($apuesta['tipo']); ?> </span>
                        <span class="apuesta-eleccion"> <?php echo $apuesta['eleccion']; ?></span>:
                        <span class="apuesta-cantidad"> <?php echo number_format($apuesta['cantidad'], 2, ',','.');?> € </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="accion" value="jugar">
            <button type="submit" class="boton-girar"> GIRAR RULETA</button>
        </form>
    </section>

    <?php if ($ronda !== null): ?>
        <section class="resultado-ronda">
            <h2>Resultado de la ronda</h2>
            <p>Número: <strong> <?php echo $ronda['numero']; ?> </strong></p>
            <p>Color: <strong> <?php echo ucfirst($ronda['color']); ?> </strong></p>

            <?php if ($ronda['paridad'] !== null): ?>
                <p>Paridad: <strong> <?php echo ucfirst($ronda['paridad']); ?> </strong></p>
            <?php endif; ?>

            <?php if ($ronda['docena'] !== null): ?>
                <p>Docena: <strong><?php echo ucfirst($ronda['docena']); ?> </strong></p>
            <?php endif; ?>

            <?php if ($ronda['altoBajo'] !== null): ?>
                <p>Alto/Bajo: <strong> <?php echo ucfirst($ronda['altoBajo']); ?> </strong></p>
            <?php endif; ?>

            <p>Resultado económico: <strong>
                    <?php
                    if ($ronda['resultado'] >= 0) echo '+';

                    echo number_format($ronda['resultado'], 2, ',','.');
                    ?>
                    €
                </strong></p>
        </section>

    <?php endif; ?>

    <section class="historial">
        <h2>Historial de apuestas</h2>
        <?php if (empty($historial)): ?>
            <p>No hay apuestas realizadas todavía.</p>
        <?php else: ?>

            <?php foreach ($historial as $indice => $ronda): ?>
                <article class="ronda-historial">
                    <h3>Ronda <?php echo $indice + 1; ?></h3>
                    <p>Número: <?php echo $ronda['numero']; ?></p>
                    <p>Color: <?php echo ucfirst($ronda['color']); ?></p>
                    <p>Resultado:
                        <?php
                        if ($ronda['resultado'] >= 0) echo '+';
                        echo number_format($ronda['resultado'], 2, ',', '.');
                        ?>
                        €
                    </p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</body>
</html>
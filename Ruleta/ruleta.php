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

//Orden de los números de una ruleta europea
$ordenRuleta = [
    0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13,
    36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14,
    31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26
];

//Calculamos el total de dinero de las apuestas preparadas
$totalPendiente = 0;

foreach ($apuestasPendientes as $apuesta) {
    $totalPendiente += $apuesta['cantidad'];
}

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

    //No permitimos girar si no hay apuestas preparadas
    if (empty($apuestas)) {
        return [
            'error' => true,
            'mensaje' => 'Debes preparar al menos una apuesta antes de girar la ruleta.'
        ];
    }

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
            if (!cantidadValida($cantidad, $dinero)) {
                if ($cantidad < 1) {
                    $mensaje = 'La apuesta mínima es de 1 €.';
                } else {
                    $mensaje = 'No puedes apostar más dinero del que tienes disponible.';
                }

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
                            if (!is_numeric($eleccion) || (int)$eleccion < 0 || (int)$eleccion > 36) {
                                $eleccionValida = false;
                            } else {
                                $eleccion = (int)$eleccion;
                            }
                            break;

                        case 'color':
                            if (!in_array($eleccion, ['rojo', 'negro'], true)) {
                                $eleccionValida = false;
                            }
                            break;

                        case 'paridad':
                            if (!in_array($eleccion, ['par', 'impar'], true)) {
                                $eleccionValida = false;
                            }
                            break;

                        case 'docena':
                            if (!in_array($eleccion, ['primera', 'segunda', 'tercera'], true)) {
                                $eleccionValida = false;
                            }
                            break;

                        case 'altoBajo':
                            if (!in_array($eleccion, ['alto', 'bajo'], true)) {
                                $eleccionValida = false;
                            }
                            break;
                    }

                    if (!$eleccionValida) {
                        $mensaje = 'La opción seleccionada no es válida.';
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
}

//Actualizamos el total pendiente
$totalPendiente = 0;

foreach ($apuestasPendientes as $apuesta) {
    $totalPendiente += $apuesta['cantidad'];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruleta Europea</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="ruleta.css">
</head>

<body data-resultado="<?php echo $ronda !== null ? $ronda['numero'] : ''; ?>" data-color="<?php echo $ronda !== null ? $ronda['color'] : ''; ?>">

    <header class="cabecera">
        <div class="marca">
            <div class="marca-icono">R</div>
            <div>
                <span class="marca-superior">CASINO</span>
                <h1>Ruleta Europea</h1>
            </div>
        </div>

        <div class="saldo">
            <span class="saldo-label">SALDO DISPONIBLE</span>
            <strong> <?php echo number_format($dinero, 2, ',', '.'); ?> €</strong>
        </div>
    </header>

    <?php if ($mensaje !== null): ?>

        <div class="mensaje <?php echo $tipoMensaje; ?>">
            <span class="mensaje-icono">
                <?php
                if ($tipoMensaje === 'error') {
                    echo '!';
                } elseif ($tipoMensaje === 'exito') {
                    echo '✓';
                } else {
                    echo 'i';
                }
                ?>
            </span>

            <span>
                <?php echo htmlspecialchars($mensaje); ?>
            </span>
        </div>

    <?php endif; ?>

    <main class="contenedor">
        <section class="zona-principal">
            <div class="cabecera-seccion">
                <div>
                    <span class="etiqueta-seccion">EUROPEAN TABLE</span>
                    <h2>Ruleta</h2>
                </div>
                <div class="indicador-live"><span></span>MESA ABIERTA</div>
            </div>
            <div class="zona-ruleta">
                <div class="ruleta-contenedor">
                    <div class="puntero-ruleta"><span></span></div>
                    <div class="ruleta-sombra">
                        <div class="ruleta-externa">
                            <div class="ruleta-rotor" id="ruletaRotor">
                                <div class="aro-numeros">
                                    <?php foreach ($ordenRuleta as $indice => $numero): ?>
                                        <?php
                                        $angulo = $indice * (360 / count($ordenRuleta));
                                        $colorNumero = obtenerColor($numero);
                                        ?>
                                        <div class="numero-ruleta numero-<?php echo $colorNumero; ?>" style="--angulo: <?php echo $angulo; ?>deg;">
                                            <span><?php echo $numero; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="decoracion-ruleta decoracion-1"></div>
                                <div class="decoracion-ruleta decoracion-2"></div>
                                <div class="decoracion-ruleta decoracion-3"></div>
                                <div class="decoracion-ruleta decoracion-4"></div>
                                <div class="decoracion-ruleta decoracion-5"></div>
                                <div class="decoracion-ruleta decoracion-6"></div>
                                <div class="decoracion-ruleta decoracion-7"></div>
                                <div class="decoracion-ruleta decoracion-8"></div>

                                <div class="ruleta-centro-externo">
                                    <div class="ruleta-centro">
                                        <div class="logo-centro">
                                            <span>R</span>
                                        </div>
                                        <strong>
                                            <?php
                                            if ($ronda !== null) {
                                                echo $ronda['numero'];
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </strong>
                                        <small>EUROPEAN</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bola" id="bola"></div>
                </div>

                <div class="resultado-principal">
                    <span class="resultado-label">ÚLTIMO RESULTADO</span>
                    <div class="resultado-numero <?php echo $ronda !== null ? 'resultado-' . $ronda['color'] : ''; ?>">
                        <?php
                        if ($ronda !== null) {
                            echo $ronda['numero'];
                        } else {
                            echo '-';
                        }
                        ?>
                    </div>

                    <?php if ($ronda !== null): ?>
                        <div class="resultado-datos">
                            <span><?php echo ucfirst($ronda['color']); ?></span>

                            <?php if ($ronda['paridad'] !== null): ?>
                                <span><?php echo ucfirst($ronda['paridad']); ?></span>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <div class="resultado-datos"><span>Esperando giro</span></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="panel-apuestas">
            <div class="cabecera-apuestas">
                <div>
                    <span class="etiqueta-seccion">PLACE YOUR BET</span>
                    <h2>Realizar apuesta</h2>
                </div>
                <div class="multiplicadores">
                    <span>NÚMERO <b>36:1</b></span>
                    <span>COLOR <b>2:1</b></span>
                    <span>DOCENA <b>3:1</b></span>
                </div>
            </div>

            <form method="POST" class="formulario-apuesta" id="formularioApuesta">
                <input type="hidden" name="tipo" id="tipoApuesta" value="">
                <input type="hidden" name="eleccion" id="eleccionApuesta" value="">
                <div class="mesa-ruleta">
                    <div class="mesa-numeros">
                        <button type="button" class="casilla-numero casilla-cero" data-tipo="numero" data-eleccion="0">0</button>
                        <div class="grid-numeros">
                            <?php for ($fila = 3; $fila >= 1; $fila--): ?>
                                <?php for ($numero = $fila; $numero <= 36; $numero += 3): ?>
                                    <button type="button" class="casilla-numero <?php echo obtenerColor($numero); ?>" data-tipo="numero" data-eleccion="<?php echo $numero; ?>">
                                        <?php echo $numero; ?>
                                    </button>
                                <?php endfor; ?>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mesa-docenas">

                        <button type="button" class="apuesta-grande" data-tipo="docena" data-eleccion="primera">
                            <strong>1ª DOCENA</strong>
                            <span>1 - 12</span>
                            <small>3:1</small>
                        </button>

                        <button type="button" class="apuesta-grande" data-tipo="docena" data-eleccion="segunda"
                        >
                            <strong>2ª DOCENA</strong>
                            <span>13 - 24</span>
                            <small>3:1</small>
                        </button>

                        <button type="button" class="apuesta-grande" data-tipo="docena" data-eleccion="tercera">
                            <strong>3ª DOCENA</strong>
                            <span>25 - 36</span>
                            <small>3:1</small>
                        </button>

                    </div>

                    <div class="mesa-externas">

                        <button type="button" class="apuesta-externa" data-tipo="altoBajo" data-eleccion="bajo">
                            <strong>1 - 18</strong>
                            <span>BAJO</span>
                        </button>

                        <button type="button" class="apuesta-externa" data-tipo="paridad" data-eleccion="par">
                            <strong>PAR</strong>
                            <span>EVEN</span>
                        </button>

                        <button type="button" class="apuesta-externa color-rojo" data-tipo="color" data-eleccion="rojo"
                        >
                            <strong>ROJO</strong>
                            <span>RED</span>
                        </button>

                        <button type="button" class="apuesta-externa color-negro" data-tipo="color" data-eleccion="negro">
                            <strong>NEGRO</strong>
                            <span>BLACK</span>
                        </button>

                        <button type="button" class="apuesta-externa" data-tipo="paridad" data-eleccion="impar">
                            <strong>IMPAR</strong>
                            <span>ODD</span>
                        </button>

                        <button type="button" class="apuesta-externa" data-tipo="altoBajo" data-eleccion="alto">
                            <strong>19 - 36</strong>
                            <span>ALTO</span>
                        </button>
                    </div>
                </div>

                <div class="zona-control-apuesta">
                    <div class="apuesta-seleccionada">
                        <span class="control-label">APUESTA SELECCIONADA</span>
                        <strong id="apuestaSeleccionada">Selecciona una casilla</strong>
                    </div>

                    <div class="entrada-cantidad">
                        <label for="cantidad">CANTIDAD</label>
                        <div class="input-euros">
                            <input type="number" id="cantidad" name="cantidad" min="1" max="<?php echo number_format($dinero, 2, '.', ''); ?>" step="0.01" placeholder="0,00" required><span>€</span>
                        </div>
                    </div>
                    <button type="submit" class="boton-preparar" id="botonPreparar" disabled><span>+</span>PREPARAR APUESTA</button>
                </div>
            </form>
        </section>
    </main>

    <section class="zona-secundaria">
        <details class="panel-colapsable apuestas-preparadas" <?php if (!empty($apuestasPendientes)) echo 'open'; ?> >
            <summary>
                <div class="summary-titulo">
                    <div class="summary-icono">€</div>
                    <div>
                        <span>BET SLIP</span>
                        <h2>Apuestas preparadas</h2>
                    </div>
                </div>

                <div class="summary-datos">
                    <strong><?php echo count($apuestasPendientes); ?></strong>
                    <span>apuestas</span>
                    <strong><?php echo number_format($totalPendiente, 2, ',', '.'); ?> €</strong>
                    <span class="flecha">▾</span>
                </div>
            </summary>

            <div class="contenido-colapsable">
                <?php if (empty($apuestasPendientes)): ?>
                    <div class="vacio">
                        <div class="vacio-icono">+</div>
                        <h3>No tienes apuestas preparadas</h3>
                        <p>Selecciona una casilla de la mesa para preparar tu primera apuesta.</p>
                    </div>
                <?php else: ?>

                    <div class="lista-apuestas">
                        <?php foreach ($apuestasPendientes as $indice => $apuesta): ?>
                            <div class="apuesta-card">
                                <div class="apuesta-numero"><?php echo $indice + 1; ?></div>
                                <div class="apuesta-info">
                                    <span>
                                        <?php

                                        switch ($apuesta['tipo']) {
                                            case 'numero':
                                                echo 'Número';
                                                break;

                                            case 'color':
                                                echo 'Color';
                                                break;

                                            case 'paridad':
                                                echo 'Paridad';
                                                break;

                                            case 'docena':
                                                echo 'Docena';
                                                break;

                                            case 'altoBajo':
                                                echo 'Alto / Bajo';
                                                break;
                                        }
                                        ?>
                                    </span>

                                    <strong><?php echo ucfirst($apuesta['eleccion']); ?></strong>
                                </div>
                                <div class="apuesta-cantidad"><?php echo number_format($apuesta['cantidad'], 2, ',', '.'); ?> €</div>
                            </div>
                        <?php endforeach; ?>

                    </div>

                    <div class="resumen-apuestas">
                        <div>
                            <span>Total apostado</span>
                            <strong><?php echo number_format($totalPendiente, 2, ',', '.'); ?> €</strong>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="accion" value="jugar">
                            <button type="submit" class="boton-girar" id="botonGirar">
                            <span class="boton-girar-icono">◉</span>GIRAR RULETA</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </details>

        <?php if ($ronda !== null): ?>
            <details class="panel-colapsable resultado-ronda" open>
                <summary>
                    <div class="summary-titulo">
                        <div class="resultado-mini <?php echo $ronda['color']; ?>"><?php echo $ronda['numero']; ?></div>
                        <div>
                            <span>ROUND RESULT</span>
                            <h2>Resultado de la ronda</h2>
                        </div>
                    </div>
                    <div class="resultado-economico <?php echo $ronda['resultado'] >= 0 ? 'positivo' : 'negativo'; ?>">
                        <?php if ($ronda['resultado'] >= 0) echo '+'; ?>
                        <?php echo number_format($ronda['resultado'], 2, ',', '.'); ?> €
                    </div>
                </summary>

                <div class="contenido-colapsable">
                    <div class="resultado-detalles">
                        <div class="detalle-resultado">
                            <span>Número</span>
                            <strong><?php echo $ronda['numero']; ?></strong>
                        </div>
                        <div class="detalle-resultado">
                            <span>Color</span>
                            <strong><?php echo ucfirst($ronda['color']); ?></strong>
                        </div>

                        <?php if ($ronda['paridad'] !== null): ?>
                            <div class="detalle-resultado">
                                <span>Paridad</span>
                                <strong><?php echo ucfirst($ronda['paridad']); ?></strong>
                            </div>
                        <?php endif; ?>

                        <?php if ($ronda['docena'] !== null): ?>
                            <div class="detalle-resultado">
                                <span>Docena</span>
                                <strong><?php echo ucfirst($ronda['docena']); ?></strong>
                            </div>
                        <?php endif; ?>

                        <?php if ($ronda['altoBajo'] !== null): ?>
                            <div class="detalle-resultado">
                                <span>Alto / Bajo</span>
                                <strong><?php echo ucfirst($ronda['altoBajo']); ?></strong>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="resultado-apuestas">
                        <h3>Apuestas de esta ronda</h3>
                        <?php foreach ($ronda['apuestas'] as $apuesta): ?>
                            <div class="resultado-apuesta <?php echo $apuesta['ganada'] ? 'ganada' : 'perdida'; ?>">
                                <div>
                                    <strong><?php echo ucfirst($apuesta['eleccion']); ?></strong>

                                    <span><?php echo number_format($apuesta['cantidad'], 2, ',', '.'); ?> €</span>
                                </div>

                                <strong>
                                    <?php if ($apuesta['ganada']): ?>
                                        +<?php echo number_format($apuesta['premio'], 2, ',', '.'); ?> €
                                    <?php else: ?>Perdida<?php endif; ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </details>
        <?php endif; ?>
        <details class="panel-colapsable historial">
            <summary>
                <div class="summary-titulo">
                    <div class="summary-icono">↺</div>
                    <div>
                        <span>GAME HISTORY</span>
                        <h2>Historial</h2>
                    </div>
                </div>

                <div class="summary-datos">
                    <strong><?php echo count($historial); ?></strong>
                    <span>rondas</span>
                    <span class="flecha">▾</span>
                </div>
            </summary>

            <div class="contenido-colapsable">
                <?php if (empty($historial)): ?>
                    <div class="vacio">
                        <div class="vacio-icono">↺</div>
                        <h3>No hay apuestas realizadas todavía</h3>
                        <p>El historial de tus rondas aparecerá aquí.</p>
                    </div>
                <?php else: ?>
                    <div class="lista-historial">
                        <?php foreach (array_reverse($historial, true) as $indice => $rondaHistorial): ?>
                            <article class="ronda-historial">
                                <div class="historial-numero">
                                    <div class="numero-historial <?php echo $rondaHistorial['color']; ?>">
                                        <?php echo $rondaHistorial['numero']; ?>
                                    </div>
                                </div>
                                <div class="historial-info">
                                    <span>Ronda <?php echo $indice + 1; ?></span>
                                    <strong>
                                        <?php echo ucfirst($rondaHistorial['color']); ?>
                                        <?php if ($rondaHistorial['paridad'] !== null): ?> · <?php echo ucfirst($rondaHistorial['paridad']); ?>
                                        <?php endif; ?>
                                    </strong>
                                </div>

                                <div class="historial-apostado">
                                    <span>Apostado</span>
                                    <strong><?php echo number_format($rondaHistorial['totalApostado'], 2, ',', '.'); ?> €</strong>
                                </div>

                                <div class="historial-resultado <?php echo $rondaHistorial['resultado'] >= 0 ? 'positivo' : 'negativo'; ?>">
                                    <span>Resultado</span>
                                    <strong>
                                        <?php if ($rondaHistorial['resultado'] >= 0) echo '+'; ?>
                                        <?php echo number_format($rondaHistorial['resultado'], 2, ',', '.'); ?> €
                                    </strong>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </details>
    </section>
    <footer class="footer">
        <span>EUROPEAN ROULETTE</span>
        <span>0 - 36 · SINGLE ZERO</span>
        <span>© Víctor Martín Pérez - 2DAW |<?php echo date('Y'); ?>
        </span>
    </footer>

    <script src="ruleta.js"></script>
</body>
</html>
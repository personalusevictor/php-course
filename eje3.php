<?php

$dinero = (float) 1000;
$historial = [];
$apuestasPendientes = [];


// Menu Principal
function menuPrincipal(): int {
    echo "==============================\n";
    echo "       RULETA\n";
    echo "0. Jugar\n";
    echo "1. Apostar a un número\n";
    echo "2. Apostar a un color\n";
    echo "3. Apostar a par o impar\n";
    echo "4. Apostar a una docena\n";
    echo "5. Apostar a alto o bajo\n";
    echo "6. Ver historial de apuestas\n";
    echo "7. Salir\n";
    echo "==============================\n";

    return (int) readline("Introduce el número de la opción: ");
}

//Menu Color - Función para elegir el color al que apostar
function menuColor(): string {
    echo "\nElige un color:\n";
    echo "1. Rojo\n";
    echo "2. Negro\n";

    while (true) {

        $opcion = (int) readline("Opción: ");

        switch ($opcion) {

            case 1:
                return 'rojo';

            case 2:
                return 'negro';

            default:
                echo "Opción no válida.\n";
        }
    }
}
//Menu Par/Impar - Función para elegir la paridad a la que apostar
function menuParidad(): string {
    echo "\nElige una opción:\n";
    echo "1. Par\n";
    echo "2. Impar\n";

    while (true) {

        $opcion = (int) readline("Opción: ");

        switch ($opcion) {

            case 1:
                return 'par';

            case 2:
                return 'impar';

            default:
                echo "Opción no válida.\n";
        }
    }
}

//Menu Docena - Función para elegir la docena a la que apostar
function menuDocena(): string {
    echo "\nElige una docena:\n";
    echo "1. Primera Docena (1-12)\n";
    echo "2. Segunda Docena (13-24)\n";
    echo "3. Tercera Docena (25-36)\n";

    while (true) {

        $opcion = (int) readline("Opción: ");

        switch ($opcion) {

            case 1:
                return 'primera';

            case 2:
                return 'segunda';

            case 3:
                return 'tercera';

            default:
                echo "Opción no válida.\n";
        }
    }
}

function menuAltoBajo(): string {
    echo "\nElige una opción:\n";
    echo "1. Alto (19-36)\n";
    echo "2. Bajo (1-18)\n";

    while (true) {

        $opcion = (int) readline("Opción: ");

        switch ($opcion) {

            case 1:
                return 'alto';

            case 2:
                return 'bajo';

            default:
                echo "Opción no válida.\n";
        }
    }
}

// Pedir Apuesta - Función para pedir la cantidad a apostar
function pedirApuesta(float $dinero): float {
    while (true) {

        $entrada = trim(readline("Cantidad a apostar: "));
        $entrada = str_replace(',', '.', $entrada);

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $entrada)) {
            echo "Introduce un número válido con máximo 2 decimales.\n";
            continue;
        }

        $apuesta = (float) $entrada;

        if ($apuesta < 1 || $apuesta > $dinero) {
            echo "La apuesta debe estar entre 1 y {$dinero}$.\n";
            continue;
        }

        return round($apuesta, 2);
    }
}

// Pedir Número - Función para pedir el número al que apostar
function pedirNumero(): int {
    while (true) {

        $entrada = readline("Número al que quieres apostar (0-36): ");

        if (!ctype_digit($entrada)) {
            echo "Introduce un número entre 0 y 36.\n";
            continue;
        }

        $numero = (int) $entrada;

        if ($numero < 0 || $numero > 36) {
            echo "El número debe estar entre 0 y 36.\n";
            continue;
        }

        return $numero;
    }
}

// Obtener Color - Función para determinar el color del número ganador
function obtenerColor(int $numero): string {
    $rojos = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

    return in_array($numero, $rojos, true)
        ? 'rojo'
        : 'negro';
}

// Obtener Paridad - Función para determinar la paridad del número ganador
function obtenerParidad(int $numero): ?string {
    if ($numero === 0) {
        return null;
    }

    return $numero % 2 === 0
        ? 'par'
        : 'impar';
}

//Obtener Docena - Función para determinar la docena del número ganador
function obtenerDocena(int $numero): ?string {
    if ($numero >= 1 && $numero <= 12) {
        return 'primera';
    }

    if ($numero >= 13 && $numero <= 24) {
        return 'segunda';
    }

    if ($numero >= 25 && $numero <= 36) {
        return 'tercera';
    }

    return null;
}

//Obtener Alto y Bajo - Función para determinar el mitades del número ganador
function obtenerAltoBajo(int $numero): ?string {
    if ($numero >= 1 && $numero <= 18) {
        return 'bajo';
    }

    if ($numero >= 19 && $numero <= 36) {
        return 'alto';
    }

    return null;
}

//Historial de Apuestas - Función para mostrar el historial de apuestas realizadas
function historialApuestas(array $historial): void {
    if (empty($historial)) {
        echo "\nNo hay apuestas realizadas todavía.\n";
        return;
    }

    echo "\n==============================\n";
    echo "      HISTORIAL\n";

    foreach ($historial as $index => $ronda) {

        echo "\nRonda " . ($index + 1) . "\n";
        echo "Número ganador: {$ronda['numero']}\n";
        echo "Color: {$ronda['color']}\n";

        if ($ronda['paridad'] !== null) {
            echo "Paridad: {$ronda['paridad']}\n";
        }

        echo "Docena: {$ronda['docena']}\n";
        echo "Alto/Bajo: {$ronda['altoBajo']}\n";

        echo "------------------------------\n";

            if (empty($ronda['apuestas'])) {
                echo "Sin apuestas en esta ronda.\n";
            } else {
                foreach ($ronda['apuestas'] as $apuesta) {

                    if ($apuesta['tipo'] === 'altoBajo') {
                        echo "Alto/Bajo | {$apuesta['eleccion']}";
                    } else {
                        echo ucfirst($apuesta['tipo']) . " {$apuesta['eleccion']}";
                    }

                    echo " | ";
                    echo $apuesta['cantidad'] . "$";
                    echo " | ";

                    if ($apuesta['ganada']) {
                        echo "GANADA";
                    } else {
                        echo "PERDIDA";
                    }

                    echo "\n";
                }

            echo "Resultado de la ronda: ";
            echo $ronda['resultado'] >= 0
                ? "+" . $ronda['resultado'] . "$\n"
                : $ronda['resultado'] . "$\n";
        }
    }
}

// Mostrar Apuestas Pendientes - Función para mostrar las apuestas preparadas antes de jugar
function mostrarApuestasPendientes(array $apuestas): void {
    if (empty($apuestas)) {
        echo "\nNo tienes apuestas preparadas.\n";
        return;
    }

    echo "\n==============================\n";
    echo "    APUESTAS PREPARADAS\n";

    $total = 0;

    foreach ($apuestas as $index => $apuesta) {

        echo ($index + 1) . ". ";
        if ($apuesta['tipo'] === 'altoBajo') {
            echo "Alto y Bajo: ";
        } else {
            echo ucfirst($apuesta['tipo']) . ": ";
        }
        echo $apuesta['eleccion'];
        echo " → ";
        echo $apuesta['eleccion'];
        echo " → ";
        echo $apuesta['cantidad'];
        echo "$\n";

        $total += $apuesta['cantidad'];
    }

    echo "------------------------------\n";
    echo "Total apostado: {$total}$\n";
}

// Main - Bucle principal del juego
while ($dinero > 0) {

    echo "\033[2J\033[H";
    echo "\nSaldo actual: {$dinero}$\n";

    mostrarApuestasPendientes($apuestasPendientes);

    $opcion = menuPrincipal();

    switch ($opcion) {
        case 0:

            $totalApostado = 0;

            foreach ($apuestasPendientes as $apuesta) {
                $totalApostado += $apuesta['cantidad'];
            }

            if ($totalApostado > $dinero) {
                echo "\nNo tienes suficiente dinero.\n";
                echo "Saldo: {$dinero}$\n";
                echo "Necesitas: {$totalApostado}$\n";
                readline("\nPresiona cualquier tecla para continuar...");
                break;
            }

            $dinero -= $totalApostado;

            $numeroGanador = random_int(0, 36);
            $colorGanador = obtenerColor($numeroGanador);
            $paridadGanadora = obtenerParidad($numeroGanador);
            $docenaGanadora = obtenerDocena($numeroGanador);
            $altoBajoGanador = obtenerAltoBajo($numeroGanador);

            echo "\n==============================\n";
            echo "       RULETA GIRANDO...\n";
            echo "Número ganador: {$numeroGanador}\n";
            echo "Color: {$colorGanador}\n";

            if ($paridadGanadora !== null) {
                echo "Paridad: {$paridadGanadora}\n";
            } else {
                echo "Paridad: ninguna\n";
            }

            echo "Docena: " . ($docenaGanadora ?? 'ninguna') . "\n";
            echo "Alto/Bajo: " . ($altoBajoGanador ?? 'ninguno') . "\n";
            echo "==============================\n";


            $gananciaRonda = 0;

            foreach ($apuestasPendientes as $index => $apuesta) {

                $ganada = false;
                $premio = 0;

                switch ($apuesta['tipo']) {

                    case 'número':
                        if ($apuesta['eleccion'] === $numeroGanador) {
                            $ganada = true;
                            $premio = $apuesta['cantidad'] * 36;
                        }
                        break;

                    case 'color':
                        if ($apuesta['eleccion'] === $colorGanador) {
                            $ganada = true;
                            $premio = $apuesta['cantidad'] * 2;
                        }
                        break;

                    case 'paridad':
                        if ($apuesta['eleccion'] === $paridadGanadora) {
                            $ganada = true;
                            $premio = $apuesta['cantidad'] * 2;
                        }
                        break;

                    case 'docena':
                        if ($apuesta['eleccion'] === $docenaGanadora) {
                            $ganada = true;
                            $premio = $apuesta['cantidad'] * 3;
                        }
                        break;

                    case 'altoBajo':
                        if ($apuesta['eleccion'] === $altoBajoGanador) {
                            $ganada = true;
                            $premio = $apuesta['cantidad'] * 2;
                        }
                        break;
                }

                $apuestasPendientes[$index]['ganada'] = $ganada;

                if ($ganada) {
                    $dinero += $premio;
                    $gananciaRonda += $premio;

                    echo "\nGANASTE: ";

                    if ($apuesta['tipo'] === 'altoBajo') {
                        echo "Alto/Bajo {$apuesta['eleccion']}";
                    } else {
                        echo ucfirst($apuesta['tipo']) . " {$apuesta['eleccion']}";
                    }

                    echo " -> +{$premio}$\n";
                } else {

                    echo "\nPERDISTE: ";

                    if ($apuesta['tipo'] === 'altoBajo') {
                        echo "Alto/Bajo {$apuesta['eleccion']}";
                    } else {
                        echo ucfirst($apuesta['tipo']) . " {$apuesta['eleccion']}";
                    }

                    echo "\n";
                }
            }

            $resultadoRonda = $gananciaRonda - $totalApostado;

            $historial[] = [
                'numero' => $numeroGanador,
                'color' => $colorGanador,
                'paridad' => $paridadGanadora,
                'docena' => $docenaGanadora,
                'altoBajo' => $altoBajoGanador,
                'apuestas' => $apuestasPendientes,
                'resultado' => $resultadoRonda
            ];

            echo "\n==============================\n";
            echo "Resultado de la ronda: ";

            if ($resultadoRonda >= 0) {
                echo "+{$resultadoRonda}$\n";
            } else {
                echo "{$resultadoRonda}$\n";
            }

            echo "Saldo actual: {$dinero}$\n";
            echo "==============================\n";

            $apuestasPendientes = [];

            readline("\nPresiona cualquier tecla para continuar...");

            break;
        case 1:
            $numero = pedirNumero();
            $cantidad = (float) pedirApuesta($dinero);
            $apuestasPendientes[] = [
                'tipo' => 'número',
                'eleccion' => $numero,
                'cantidad' => $cantidad
            ];

            echo "\nApuesta preparada:\n";
            echo "{$cantidad}$ al número {$numero}.\n";

            break;

        case 2:
            $color = menuColor();
            $cantidad = pedirApuesta($dinero);
            $apuestasPendientes[] = [
                'tipo' => 'color',
                'eleccion' => $color,
                'cantidad' => $cantidad
            ];

            echo "\nApuesta preparada:\n";
            echo "{$cantidad}$ al color {$color}.\n";

            break;

        case 3:
            $paridad = menuParidad();
            $cantidad = pedirApuesta($dinero);
            $apuestasPendientes[] = [
                'tipo' => 'paridad',
                'eleccion' => $paridad,
                'cantidad' => $cantidad
            ];

            echo "\nApuesta preparada:\n";
            echo "{$cantidad}$ a {$paridad}.\n";

            break;

        case 4:
            $docena = menuDocena();
            $cantidad = pedirApuesta($dinero);
            $apuestasPendientes[] = [
                'tipo' => 'docena',
                'eleccion' => $docena,
                'cantidad' => $cantidad
            ];

            echo "\nApuesta preparada:\n";
            echo "{$cantidad}$ a la docena {$docena}.\n";

            break;

        case 5:
            $altoBajo = menuAltoBajo();
            $cantidad = pedirApuesta($dinero);
            $apuestasPendientes[] = [
                'tipo' => 'altoBajo',
                'eleccion' => $altoBajo,
                'cantidad' => $cantidad
            ];

            echo "\nApuesta preparada:\n";
            echo "{$cantidad}$ a {$altoBajo}.\n";

            break;
        case 6:
            historialApuestas($historial);
            readline("\nPresiona cualquier tecla para continuar...");
            break;

        case 7:
            echo "\nGracias por jugar.\n";
            exit;

        default:
            echo "\nOpción no válida.\n";
    }
}

echo "\nTe has quedado sin dinero. ¡Gracias por jugar!\n";

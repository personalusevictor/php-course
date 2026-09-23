<?php

$dinero = 1000;
$historial = [];
$apuestasPendientes = [];

function menuPrincipal(): int{
    echo "==============================\n";
    echo "       RULETA\n";
    echo "1. Apostar a un número\n";
    echo "2. Apostar a un color\n";
    echo "3. Apostar a par o impar\n";
    echo "4. Apostar\n";
    echo "5. Ver historial de apuestas\n";
    echo "6. Salir\n";
    echo "==============================\n";

    return (int) readline("Introduce el número de la opción: ");
}

function pedirApuesta(int $dinero): int {
    while (true) {

        $entrada = readline("Cantidad a apostar: ");

        if (!ctype_digit($entrada)) {
            echo "Introduce un número válido.\n";
            continue;
        }

        $apuesta = (int) $entrada;

        if ($apuesta < 1 || $apuesta > $dinero) {
            echo "La apuesta debe estar entre 1 y {$dinero}$.\n";
            continue;
        }

        return $apuesta;
    }
}

function menuColor(): string {
    echo "\nElige un color:\n";
    echo "1. Rojo\n";
    echo "2. Negro\n";
    echo "3. Verde\n";

    while (true) {

        $opcion = (int) readline("Opción: ");

        switch ($opcion) {

            case 1:
                return 'rojo';

            case 2:
                return 'negro';

            case 3:
                return 'verde';

            default:
                echo "Opción no válida.\n";
        }
    }
}

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

function obtenerColor(int $numero): string {
    if ($numero === 0) {
        return 'verde';
    }

    $rojos = [
        1, 3, 5, 7, 9,
        12, 14, 16, 18,
        19, 21, 23, 25, 27,
        30, 32, 34, 36
    ];

    return in_array($numero, $rojos, true)
        ? 'rojo'
        : 'negro';
}

function obtenerParidad(int $numero): ?string {
    if ($numero === 0) {
        return null;
    }

    return $numero % 2 === 0
        ? 'par'
        : 'impar';
}

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

        echo "------------------------------\n";

        foreach ($ronda['apuestas'] as $apuesta) {

            echo ucfirst($apuesta['tipo']) . ": ";
            echo $apuesta['eleccion'];
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

function mostrarApuestasPendientes(array $apuestas): void
{
    if (empty($apuestas)) {
        echo "\nNo tienes apuestas preparadas.\n";
        return;
    }

    echo "\n==============================\n";
    echo "    APUESTAS PREPARADAS\n";

    $total = 0;

    foreach ($apuestas as $index => $apuesta) {

        echo ($index + 1) . ". ";
        echo ucfirst($apuesta['tipo']);
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

while ($dinero > 0) {

    echo "\nSaldo actual: {$dinero}$\n";

    mostrarApuestasPendientes($apuestasPendientes);

    $opcion = menuPrincipal();

    switch ($opcion) {
        case 1:
            $numero = pedirNumero();
            $cantidad = pedirApuesta($dinero);
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

            if (empty($apuestasPendientes)) {
                echo "\nNo tienes ninguna apuesta preparada.\n";
                break;
            }

            $totalApostado = 0;

            foreach ($apuestasPendientes as $apuesta) {
                $totalApostado += $apuesta['cantidad'];
            }

            if ($totalApostado > $dinero) {

                echo "\nNo tienes suficiente dinero.\n";
                echo "Saldo: {$dinero}$\n";
                echo "Necesitas: {$totalApostado}$\n";

                break;
            }

            $dinero -= $totalApostado;

            $numeroGanador = random_int(0, 36);

            $colorGanador = obtenerColor($numeroGanador);
            $paridadGanadora = obtenerParidad($numeroGanador);

            echo "\n==============================\n";
            echo "       RULETA GIRANDO...\n";

            echo "Número ganador: {$numeroGanador}\n";
            echo "Color: {$colorGanador}\n";

            if ($paridadGanadora !== null) {
                echo "Paridad: {$paridadGanadora}\n";
            } else {
                echo "Paridad: ninguna\n";
            }

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

                            if ($apuesta['eleccion'] === 'verde') {
                                $premio = $apuesta['cantidad'] * 36;
                            } else {
                                $premio = $apuesta['cantidad'] * 2;
                            }
                        }

                        break;

                    case 'paridad':

                        if ($apuesta['eleccion'] === $paridadGanadora) {

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
                    echo ucfirst($apuesta['tipo']);
                    echo " {$apuesta['eleccion']}";
                    echo " → +{$premio}$\n";

                } else {

                    echo "\nPERDISTE: ";
                    echo ucfirst($apuesta['tipo']);
                    echo " {$apuesta['eleccion']}";
                    echo "\n";
                }
            }

            $resultadoRonda = $gananciaRonda - $totalApostado;

            $historial[] = [
                'numero' => $numeroGanador,
                'color' => $colorGanador,
                'paridad' => $paridadGanadora,
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
            
            break;

        case 5:
            historialApuestas($historial);
            break;

        case 6:
            echo "\nGracias por jugar.\n";
            exit;

        default:
            echo "\nOpción no válida.\n";
    }
}

echo "\nTe has quedado sin dinero. ¡Gracias por jugar!\n";
?>
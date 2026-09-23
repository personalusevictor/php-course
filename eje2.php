<?php

$avg = 0;
$nota_minima = 10;

static $CANTIDAD_NOTAS = readline('Introduce la cantidad de notas: ');

for($i = 1; $i <= $CANTIDAD_NOTAS; $i++) {
    $num = readline("Introduce la nota Nº$i: ");
    $avg += $num;

    $nota_minima = min($nota_minima, $num);
}

$avg /= $CANTIDAD_NOTAS;
$resultado = ($nota_minima < 5) ? min($avg, 4) : $avg;

print("La nota media es: " . round($resultado) . "\n");
?>
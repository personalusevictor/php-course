<?php
$frase1 = strtolower(readline('Introduce la primera frase: '));
$frase2 = strtolower(readline('Introduce la segunda frase: '));

$coincidencia = str_contains($frase1, $frase2);
$coincidencia ? print("\nLa segunda frase se encuentra en la primera frase\n") : print("\nLa segunda frase no se encuentra en la primera frase\n");

$numcar = mb_strlen($frase1);
print("\nLa primera frase tiene $numcar caracteres");

$numletras = str_word_count($frase1);
print("\nLa primera frase tiene $numletras palabras\n");

if($coincidencia) {
    $remplazo = str_replace($frase2, '', $frase1);
    print("\nLa primera frase quitando eliminando la coincidencia es: '" . trim($remplazo) . "'\n");
}
?>
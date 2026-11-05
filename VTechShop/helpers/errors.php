<?php 
    function getErrors(String $error_code) {
        $errors = [
            "db_failed" => "Error al establecer una conexión con la base de datos",
        ];

        printErrors($errors[$error_code]);
    }

    function printErrors(String $error) {
        echo $error;
    }
?>
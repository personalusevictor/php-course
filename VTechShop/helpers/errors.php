<?php 
    function getErrors(String $error_code) {
        $errors = [
            "db_failed" => "Error al establecer una conexión con la base de datos",
            "query_failed" => "Error: no se ha podido recuperar el catálogo de productos",
            "others" => "Se ha producido un error desconocido",
        ];

        if(!array_key_exists($error_code, $errors)) {
            $error_code = "others";
        }

        echo '<div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0; border-radius: 5px;">';
        echo htmlspecialchars($errors[$error_code]);
        echo '</div>';
    }
?>
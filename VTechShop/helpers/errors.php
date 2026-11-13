<?php 
    function getErrors(String $error_code) {
        $errors = [
            "db_failed" => "Error, al establecer una conexión con la base de datos.",
            "query_failed" => "Error, no se ha podido recuperar el catálogo de productos.",
            "missing_fields" => "Por favor, rellena todos los campos obligatorios.",
            "invalid_length" => "Uno o más campos no cumplen la longitud permitida.",
            "invalid_email" => "El formato del correo electrónico no es válido.",
            "invalid_verify" => "Las contraseñas no coinciden.",
            "invalid_currency" => "La moneda seleccionada no es válida.",
            "invalid_action" => "La acción solicitada no es válida.",
            "invalid_credentials" => "El correo electrónico o la contraseña son incorrectas.",
            "user_exists" => "El nombre de usuario o el correo electrónico ya existe.",
            "others" => "Se ha producido un error desconocido.",
        ];

        if(!array_key_exists($error_code, $errors)) {
            $error_code = "others";
        }

        echo '<div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0; border-radius: 5px; display: inline-block">';
        echo htmlspecialchars($errors[$error_code]);
        echo '</div>';
    }
?>
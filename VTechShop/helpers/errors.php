<?php 
    function getError(String $error_code): string {
        $errors = [
            "db_failed" => "Error, al establecer una conexión con la base de datos.",
            "query_failed" => "No se ha podido recuperar el catálogo de productos.",
            "missing_fields" => "Por favor, rellena todos los campos obligatorios.",
            "invalid_length" => "Uno o más campos no cumplen la longitud permitida.",
            "invalid_email" => "El formato del correo electrónico no es válido.",
            "invalid_verify" => "Las contraseñas no coinciden.",
            "invalid_currency" => "La moneda seleccionada no es válida.",
            "invalid_action" => "La acción solicitada no es válida.",
            "invalid_credentials" => "El correo electrónico o la contraseña son incorrectas.",
            "user_exists" => "El nombre de usuario o el correo electrónico ya existe.",
            "out_of_stock" => "Uno o más productos no tienen suficiente stock disponible.",
            "empty_cart" => "Tu carrito está vacío.",
            "login_required" => "Debes iniciar sesión para competar esta acción.",
            "order_failed" => "No se puede procesar el pedido. Inténtalo de nuevo",
            "others" => "Se ha producido un error desconocido.",
        ];

        return $errors[$error_code] ?? $errors['others'];
    }

    function render_error(?string $error_code): void {
        if (!$error_code) return;
        echo "<div class='alert alert-error'>". htmlspecialchars(getError($error_code)) . "</div>";
    }
?>
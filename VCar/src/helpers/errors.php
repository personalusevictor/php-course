<?php

function getErrors(string $codigo) {
    $errors = [
        "db_failed" => "Error al establecer la conexión con la base de datos.",
        "auth_invalid_credentials" => "El correo electrónico o la contraseña son incorrectos.",
        "auth_unauthorized_role" => "Acceso no autorizado. El rol de usuario no está reconocido.",
        "auth_register_exists" => "Las credenciales introducidas ya están registradas en el sistema.",
        "validation_birth_date" => "La fecha de nacimiento introducida no es válida.",
        "validation_underage" => "Debes ser mayor de edad para poder registrarte.",
        "file_upload_failed" => "El archivo no se ha subido correctamente o no se ha seleccionado ninguno.",
        "file_invalid_image" => "El archivo seleccionado no es una imagen válida.",
        "file_invalid_format" => "El formato del archivo no es compatible o válido.",
        "validation_invalid_dates" => "El rango de fechas introducido no es válido (la fecha de fin es anterior a la de inicio).",
        "availability_vehicle_closed" => "El vehículo seleccionado no está disponible en las fechas indicadas debido a un mantenimiento o cierre programado.",
        "availability_check_failed" => "Error al comprobar la disponibilidad.",
    ];
    printErrors($errors[$codigo] ?? "Ha ocurrido un error desconocido.");
}

function printErrors(string $message): void {
    echo htmlspecialchars($message);
}
?>
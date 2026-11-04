<?php
date_default_timezone_set('Atlantic/Canary');

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): void {
    if (!is_string($_POST['csrf_token'] ?? null) || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Formulario caducado o no válido. Vuelve a cargar la página.');
    }
}

function valid_date(string $value): bool {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value;
}

function valid_period(string $start, string $end): bool {
    return valid_date($start) && valid_date($end) && $start >= date('Y-m-d') && $end >= $start;
}

function input(string $name): string {
    return is_string($_POST[$name] ?? null) ? trim($_POST[$name]) : '';
}

function vehicle_label(string $value): string {
    return ['manual' => 'Manual', 'automatic' => 'Automático', 'gasoline' => 'Gasolina', 'diesel' => 'Diésel', 'hybrid' => 'Híbrido', 'electric' => 'Eléctrico'][$value] ?? $value;
}

function display_date(string $value): string {
    return (new DateTimeImmutable($value))->format('d/m/Y');
}

function valid_identity(string $value): bool {
    if (!preg_match('/^(?:[0-9]{8}|[XYZ][0-9]{7})[A-Z]$/', $value)) {
        return false;
    }
    $number = strtr(substr($value, 0, -1), ['X' => '0', 'Y' => '1', 'Z' => '2']);
    return 'TRWAGMYFPDXBNJZSQVHLCKE'[(int)$number % 23] === substr($value, -1);
}

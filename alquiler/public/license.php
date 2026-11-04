<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Acceso denegado.');
}
require_once __DIR__ . '/../src/config/connection.php';
$user = (int)$_SESSION['user_id'];
if (($_SESSION['role_id'] ?? null) == 1) {
    $user = (int)($_GET['user_id'] ?? $user);
}
$stmt = $conn->prepare('SELECT driver_license FROM customers WHERE users_id = ?');
$stmt->execute([$user]);
$name = $stmt->fetchColumn();
$path = $name ? __DIR__ . '/../storage/licenses/' . basename($name) : '';
if (!$name || !is_file($path)) {
    http_response_code(404);
    exit('No se ha encontrado el carnet.');
}
header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($path));
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
readfile($path);

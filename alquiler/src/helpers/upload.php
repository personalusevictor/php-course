<?php

function upload_driver_license(array $file): ?string {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }
    
    $tmp_name = $file['tmp_name'];

    $image_info = @getimagesize($tmp_name);
    if ($image_info === false || $image_info[0] > 5000 || $image_info[1] > 5000) {
        return null;
    }

    $mime_type = $image_info['mime'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($mime_type, $allowed_mimes)) {
        return null;
    }

    if (!extension_loaded('gd')) {
        return null;
    }

    $upload_dir = __DIR__ . '/../../storage/licenses/';

    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0700, true)) {
            return null;
        }
    }

    if (!is_writable($upload_dir)) {
        error_log('VCar: la carpeta de carnets no permite escritura al usuario del servidor.');
        return null;
    }

    switch ($mime_type) {
        case 'image/jpeg':
            $image_resource = @imagecreatefromjpeg($tmp_name);
            break;
        case 'image/png':
            $image_resource = @imagecreatefrompng($tmp_name);
            if ($image_resource) {
                imagepalettetotruecolor($image_resource);
                imagealphablending($image_resource, true);
                imagesavealpha($image_resource, true);
            }
            break;
        case 'image/webp':
            $image_resource = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp_name) : false;
            break;
        default:
            return null;
    }

    if (!$image_resource) {
        return null;
    }

    $unique_name_file = 'license_' . bin2hex(random_bytes(16)) . '.webp';
    $full_path = $upload_dir . $unique_name_file;

    if (function_exists('imagewebp')) {
        $saved = imagewebp($image_resource, $full_path, 80);
    } else {
        $full_path = str_replace('.webp', '.jpg', $full_path);
        $unique_name_file = str_replace('.webp', '.jpg', $unique_name_file);
        $saved = imagejpeg($image_resource, $full_path, 85);
    }

    if (!$saved) {
        return null;
    }

    return $unique_name_file;
}

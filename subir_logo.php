<?php
// ========================================
// subir_logo.php
// Recibe una imagen vía AJAX y la guarda como
// logo_izquierdo.* o logo_derecho.* dentro de /logos
// ========================================
session_start();
include 'config.php';

header('Content-Type: application/json; charset=utf-8');

function responder($ok, $mensaje, $extra = []) {
    echo json_encode(array_merge(['success' => $ok, 'mensaje' => $mensaje], $extra));
    exit;
}

// ===== VERIFICACIÓN DE ROL EN EL SERVIDOR (SEGURIDAD EXTREMA) =====
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    responder(false, 'Debes iniciar sesión para cambiar el logo.');
}

// ✅ SOLO SI ES ADMIN PUEDE SUBIR IMÁGENES
if ($_SESSION['usuario_rol'] !== 'admin') {
    http_response_code(403);
    responder(false, 'No tienes permisos de administrador para cambiar el logo.');
}

// ===== VALIDAR SLOT =====
$slot = $_POST['slot'] ?? '';
if (!in_array($slot, ['izquierdo', 'derecho'], true)) {
    http_response_code(400);
    responder(false, 'Slot de logo no válido.');
}

// ===== VALIDAR QUE SE HAYA SUBIDO UN ARCHIVO CORRECTAMENTE =====
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    responder(false, 'No se recibió ningún archivo válido.');
}

$archivo = $_FILES['logo'];

// ===== LÍMITE DE TAMAÑO: 2 MB =====
$maxBytes = 2 * 1024 * 1024;
if ($archivo['size'] > $maxBytes) {
    http_response_code(400);
    responder(false, 'La imagen no debe superar 2 MB.');
}

// ===== VALIDAR TIPO REAL DEL ARCHIVO =====
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $archivo['tmp_name']);
finfo_close($finfo);

$extensionesPermitidas = [
    'image/png'  => 'png',
    'image/jpeg' => 'jpg',
    'image/webp' => 'webp',
];

if (!isset($extensionesPermitidas[$mime])) {
    http_response_code(400);
    responder(false, 'Formato no permitido. Usa PNG, JPG o WEBP.');
}

$extension = $extensionesPermitidas[$mime];

// ===== CARPETA DE DESTINO =====
$carpeta = __DIR__ . '/logos';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0755, true);
}

// ===== BORRAR VERSIONES ANTERIORES =====
foreach (['png', 'jpg', 'webp'] as $ext) {
    $rutaVieja = $carpeta . '/logo_' . $slot . '.' . $ext;
    if (file_exists($rutaVieja)) {
        unlink($rutaVieja);
    }
}

// ===== GUARDAR CON NOMBRE FIJO =====
$rutaDestino = $carpeta . '/logo_' . $slot . '.' . $extension;

if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    http_response_code(500);
    responder(false, 'Error al guardar la imagen en el servidor.');
}

responder(true, 'Logo actualizado correctamente.', [
    'url' => 'logos/logo_' . $slot . '.' . $extension . '?v=' . time()
]);
<?php
include 'config.php';

// ===== OBTENER DATOS DEL FORMULARIO =====
$consecutivo = trim($_POST['consecutivo']);
$usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
$fecha_elaboracion = $_POST['fecha_elaboracion'];
$tipo_incidencia = $_POST['tipo_incidencia'];
$motivo = trim($_POST['motivo']);
$dias_justificar = trim($_POST['dias_justificar']);

// Datos de firmas
$autoriza_nombre = trim($_POST['autoriza_nombre']);
$autoriza_puesto = trim($_POST['autoriza_puesto']);
$visto_bueno_nombre = trim($_POST['visto_bueno_nombre']);
$visto_bueno_puesto = trim($_POST['visto_bueno_puesto']);

// Lugar y fecha de expedición (por defecto)
$lugar_expedicion = 'TUXTLA GUTIERREZ, CHIAPAS';
$fecha_expedicion = date('Y-m-d');

// ===== VALIDACIONES =====
if ($usuario_id == 0) {
    header('Location: agregar_justificacion.php?error=⚠️ Debes seleccionar un usuario');
    exit();
}

if (empty($motivo) || empty($dias_justificar)) {
    header('Location: agregar_justificacion.php?error=⚠️ Todos los campos son obligatorios');
    exit();
}

if (empty($autoriza_nombre) || empty($autoriza_puesto) || empty($visto_bueno_nombre) || empty($visto_bueno_puesto)) {
    header('Location: agregar_justificacion.php?error=⚠️ Los campos de firmas son obligatorios');
    exit();
}

// ===== OBTENER DATOS DEL USUARIO =====
$stmt = $conn->prepare("SELECT nombre, puesto FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header('Location: agregar_justificacion.php?error=❌ Usuario no encontrado');
    exit();
}

// ===== GUARDAR EN BASE DE DATOS =====
$stmt = $conn->prepare("INSERT INTO justificaciones (
    consecutivo, usuario_id, fecha_elaboracion, tipo_incidencia, 
    motivo, dias_justificar, lugar_expedicion, fecha_expedicion,
    solicita_nombre, solicita_puesto,
    autoriza_nombre, autoriza_puesto,
    visto_bueno_nombre, visto_bueno_puesto
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sississsssssss", 
    $consecutivo, 
    $usuario_id, 
    $fecha_elaboracion, 
    $tipo_incidencia, 
    $motivo, 
    $dias_justificar,
    $lugar_expedicion, 
    $fecha_expedicion,
    $usuario['nombre'], // solicita_nombre
    $usuario['puesto'], // solicita_puesto
    $autoriza_nombre, 
    $autoriza_puesto,
    $visto_bueno_nombre, 
    $visto_bueno_puesto
);

if ($stmt->execute()) {
    $justificacion_id = $conn->insert_id;
    header('Location: vista_previa_justificacion.php?id=' . $justificacion_id);
} else {
    header('Location: agregar_justificacion.php?error=❌ Error al guardar: ' . $stmt->error);
}

$stmt->close();
$conn->close();
?>
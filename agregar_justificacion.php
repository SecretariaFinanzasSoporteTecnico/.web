<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include 'config.php';

// ===== OBTENER ID DEL USUARIO =====
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
if ($usuario_id == 0) {
    header('Location: index.php?error=Usuario no válido');
    exit;
}

// ===== OBTENER DATOS DEL USUARIO =====
$sql_usuario = "SELECT * FROM usuarios WHERE id = $usuario_id";
$result_usuario = $conn->query($sql_usuario);
if ($result_usuario->num_rows == 0) {
    header('Location: index.php?error=Usuario no encontrado');
    exit;
}
$usuario = $result_usuario->fetch_assoc();

// ===== PROCESAR REGISTRO DE JUSTIFICACIÓN =====
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $fecha_inicio = isset($_POST['fecha']) ? $_POST['fecha'] : '';
    $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
    $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : '';
    $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
    $fecha_registro = date('Y-m-d H:i:s');
    
    // Validar campos obligatorios
    if (empty($tipo) || empty($fecha_inicio) || empty($motivo)) {
        $mensaje = 'Los campos Tipo, Fecha de Inicio y Motivo son obligatorios.';
        $tipo_mensaje = 'danger';
    } else {
        // ✅ CORREGIDO: Guardar en la tabla 'justificaciones' (NO en incidencias)
        $sql_insert = "INSERT INTO justificaciones (
            usuario_id, 
            tipo_incidencia, 
            fecha_inicio,
            fecha_fin,
            motivo, 
            observaciones
        ) VALUES (
            $usuario_id, 
            '$tipo', 
            '$fecha_inicio', 
            '$fecha_fin',
            '$motivo', 
            '$observaciones'
        )";
        
        if ($conn->query($sql_insert)) {
            $mensaje = '✅ Justificación registrada correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ Error al registrar la justificación: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }
}
// EL RESTO DEL HTML Y CSS SE MANTIENE IGUAL. SOLO EL PHP DE ARRIBA CAMBIÓ.
?>
<?php
include 'config.php';

// ===== OBTENER DATOS DEL FORMULARIO =====
$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$nip = trim($_POST['nip']);
$telefono = trim($_POST['telefono']);
$area = trim($_POST['area']);
$puesto = trim($_POST['puesto']);
$tipo_contrato = trim($_POST['tipo_contrato']);
$fecha_ingreso = !empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null;

// ===== VALIDACIONES =====
if (empty($nombre) || empty($email) || empty($area) || empty($puesto)) {
    header('Location: agregar.php?error=⚠️ Los campos marcados con * son obligatorios');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: agregar.php?error=❌ El correo electrónico no tiene un formato válido');
    exit();
}

// Verificar email único
$check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header('Location: agregar.php?error=❌ El correo "' . htmlspecialchars($email) . '" ya está registrado');
    $check->close();
    $conn->close();
    exit();
}
$check->close();

// ===== INSERTAR USUARIO =====
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, nip, telefono, area, puesto, tipo_contrato, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $nombre, $email, $nip, $telefono, $area, $puesto, $tipo_contrato, $fecha_ingreso);

if ($stmt->execute()) {
    header('Location: index.php?mensaje=🎉 ¡Usuario "' . htmlspecialchars($nombre) . '" registrado con éxito!');
} else {
    header('Location: agregar.php?error=❌ Error al guardar: ' . $conn->error);
}

$stmt->close();
$conn->close();
?>
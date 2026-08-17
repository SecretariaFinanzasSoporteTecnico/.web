<?php
include 'config.php';

// ===== RECIBIR DATOS DEL FORMULARIO =====
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$area = $_POST['area'];
$puesto = $_POST['puesto'];
$nip = $_POST['nip'];

// ===== VALIDAR CAMPOS OBLIGATORIOS =====
if (empty($nombre) || empty($email)) {
    header('Location: agregar.php?error=Nombre y email son obligatorios');
    exit;
}

// ===== INSERTAR USUARIO (SOLO CAMPOS EXISTENTES) =====
$sql = "INSERT INTO usuarios (nombre, email, telefono, area, puesto, nip) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// ✅ CORREGIDO: 6 letras "ssssss" (coinciden con las 6 variables)
$stmt->bind_param("ssssss", $nombre, $email, $telefono, $area, $puesto, $nip);

if ($stmt->execute()) {
    header('Location: index.php?mensaje=Usuario agregado correctamente');
} else {
    header('Location: agregar.php?error=Error al agregar usuario: ' . $conn->error);
}

$stmt->close();
$conn->close();
?>
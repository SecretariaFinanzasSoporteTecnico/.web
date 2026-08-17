<?php
include 'config.php';

// Obtener datos del formulario
$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$telefono = trim($_POST['telefono']);

// Validar que no estén vacíos
if (empty($nombre) || empty($email)) {
    header('Location: agregar.php?error=⚠️ Por favor completa todos los campos obligatorios');
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: agregar.php?error=❌ El correo electrónico no tiene un formato válido');
    exit();
}

// Verificar si el email ya existe
$check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header('Location: agregar.php?error=❌ El correo "' . htmlspecialchars($email) . '" ya está registrado. Por favor, usa otro correo electrónico.');
    $check->close();
    $conn->close();
    exit();
}
$check->close();

// Insertar nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, telefono) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $email, $telefono);

if ($stmt->execute()) {
    header('Location: index.php?mensaje=🎉 ¡Usuario "' . htmlspecialchars($nombre) . '" registrado con éxito!');
} else {
    header('Location: agregar.php?error=❌ Error al guardar: ' . $conn->error);
}

$stmt->close();
$conn->close();
?>
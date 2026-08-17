<?php
session_start();
include 'config.php';

// Solo usuarios logueados pueden cambiar el texto
if (!isset($_SESSION['usuario_id'])) {
    exit;
}

$campo = $_POST['campo'];
$valor = $_POST['valor'];

// Lista blanca de campos permitidos (por seguridad)
$campos_permitidos = ['humanismo', 'secret', 'gobierno', 'periodo'];

if (in_array($campo, $campos_permitidos)) {
    // Actualizar o insertar el valor en la base de datos
    $sql = "INSERT INTO configuracion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $campo, $valor, $valor);
    
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "error";
    }
    $stmt->close();
} else {
    echo "Campo no permitido.";
}
$conn->close();
?>
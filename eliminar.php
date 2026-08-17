<?php
session_start();
include 'config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar que recibimos los datos necesarios
if (!isset($_GET['tabla']) || !isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$tabla = $_GET['tabla'];
$id = intval($_GET['id']);

// ✅ CORREGIDO: Lista blanca de tablas permitidas
$tablas_permitidas = ['servicios', 'usuarios', 'faltas', 'incidencias'];

if (in_array($tabla, $tablas_permitidas)) {
    $sql = "DELETE FROM $tabla WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        if ($tabla === 'servicios') {
            header('Location: gestion_servicio.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        // Si hubo un error en la base de datos
        echo "<div style='background:#f8d7da; color:#721c24; padding:20px; text-align:center; font-family:sans-serif;'>";
        echo "<h2>❌ Error al eliminar</h2>";
        echo "<p>No se pudo eliminar el registro: " . $conn->error . "</p>";
        echo "<a href='index.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#6c757d; color:#fff; text-decoration:none; border-radius:5px;'>Volver</a>";
        echo "</div>";
        exit;
    }
} else {
    die("Error: Tabla no válida.");
}
?>
<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mi_base';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    error_log("Error de conexión: " . $conn->connect_error);
    die("No se pudo conectar al sistema. Intenta más tarde.");
}

$conn->set_charset("utf8");
?>
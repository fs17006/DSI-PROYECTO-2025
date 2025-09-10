<?php
$servername = "sql305.infinityfree.com";   // Host
$username   = "if0_39903381";              // Usuario
$password   = "fYzFQCTA5mOH8";             // Contraseña
$dbname     = "if0_39903381_sistema_web";  // Base de datos

// Crear conexión
$conexion = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}
?>

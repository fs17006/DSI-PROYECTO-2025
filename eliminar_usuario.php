<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: lista_usuarios.php");
    exit();
}

require "conexion.php";

// Eliminar proveedor
$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: lista_usuarios.php");
    exit();
} else {
    echo "Error al eliminar usuarios.";
}

$stmt->close();
$conexion->close();
?>

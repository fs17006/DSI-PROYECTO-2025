<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR o CONTADOR pueden eliminar pagos
if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'CONTADOR') {
    echo "Acceso denegado.";
    exit();
}

// Validar el parámetro ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de pago no válido.";
    exit();
}

$conexion = new mysqli("localhost", "root", "", "sistema_web");
$id_pago = intval($_GET['id']);

// Verifica si el pago existe
$verificar = $conexion->prepare("SELECT id FROM pagos WHERE id = ?");
$verificar->bind_param("i", $id_pago);
$verificar->execute();
$result = $verificar->get_result();

if ($result->num_rows === 0) {
    echo "Pago no encontrado.";
    exit();
}

// Elimina el pago
$eliminar = $conexion->prepare("DELETE FROM pagos WHERE id = ?");
$eliminar->bind_param("i", $id_pago);
if ($eliminar->execute()) {
    header("Location: estado_pagos.php?mensaje=eliminado");
    exit();
} else {
    echo "Error al eliminar el pago.";
}
?>

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR puede eliminar pedidos
if ($_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";

$pedido_id = (int)($_GET['id'] ?? 0);

if ($pedido_id <= 0) {
    echo "Pedido no válido.";
    exit();
}

// Primero eliminar los detalles del pedido
$stmtDetalle = $conexion->prepare("DELETE FROM detalle_pedidos WHERE pedido_id = ?");
$stmtDetalle->bind_param("i", $pedido_id);
$stmtDetalle->execute();
$stmtDetalle->close();

// Luego eliminar el pedido
$stmtPedido = $conexion->prepare("DELETE FROM pedidos WHERE id = ?");
$stmtPedido->bind_param("i", $pedido_id);
$stmtPedido->execute();

if ($stmtPedido->affected_rows > 0) {
    $mensaje = "Pedido eliminado correctamente.";
} else {
    $mensaje = "No se pudo eliminar el pedido o ya no existe.";
}
$stmtPedido->close();
$conexion->close();

// Redirigir a la lista de pedidos con mensaje
header("Location: lista_pedidos.php?mensaje=" . urlencode($mensaje));
exit();
?>

<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // seguridad: convertir a entero

    require "conexion.php";   

    // 1. Eliminar los detalles de la factura
    $conexion->query("DELETE FROM detalle_factura WHERE factura_id = $id");

    // 2. Eliminar la factura
    $conexion->query("DELETE FROM facturas WHERE id = $id");

    header("Location: lista_facturas.php");
    exit();
}
?>

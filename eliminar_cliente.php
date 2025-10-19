<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['perfil'] !== 'ADMINISTRADOR') {
    header("Location: index.php");
    exit();
}

require "conexion.php";

$id = $_GET['id'] ?? null;

if ($id) {
    // Validar que el cliente no tenga facturas activas
    $verificar = $conexion->prepare("
        SELECT f.id FROM facturas f
        WHERE f.proveedor_id = ? -- ajustar si luego vinculas clientes a facturas
    ");
    $verificar->bind_param("i", $id);
    $verificar->execute();
    $result = $verificar->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('No se puede eliminar el cliente porque tiene facturas activas.'); window.location.href='lista_clientes.php';</script>";
    } else {
        $delete = $conexion->prepare("DELETE FROM clientes WHERE id = ?");
        $delete->bind_param("i", $id);
        if ($delete->execute()) {
            echo "<script>alert('Cliente eliminado correctamente.'); window.location.href='lista_clientes.php';</script>";
        }
    }
}
?>

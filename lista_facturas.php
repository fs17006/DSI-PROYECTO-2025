<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";
$sql = "SELECT f.id, f.numero_factura, f.fecha, f.monto, p.nombre AS proveedor 
        FROM facturas f
        INNER JOIN proveedores p ON f.proveedor_id = p.id";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Facturas</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    
    <div class="contenido">
        <h2>Lista de Facturas</h2>
        <a href="agregar_factura.php">Agregar Nueva Factura</a>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Número</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Proveedor</th>
                <th>Acciones</th>
            </tr>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $fila['numero_factura'] ?></td>
                    <td><?= $fila['fecha'] ?></td>
                    <td><?= number_format($fila['monto'], 2) ?></td>
                    <td><?= $fila['proveedor'] ?></td>
                    <td>
                        <a href="editar_factura.php?id=<?= $fila['id'] ?>">Editar</a>
                        <a href="eliminar_factura.php?id=<?= $fila['id'] ?>" onclick="return confirm('¿Seguro de eliminar esta factura?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

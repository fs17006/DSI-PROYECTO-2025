<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";

// Traer facturas con proveedor
$sql = "SELECT f.id, f.numero_factura, f.fecha, f.monto, p.nombre AS proveedor 
        FROM facturas f
        INNER JOIN proveedores p ON f.proveedor_id = p.id
        ORDER BY f.fecha DESC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Facturas</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .detalle-factura { background-color: #f9f9f9; font-size: 0.9em; }
        .detalle-factura td { padding-left: 20px; }
    </style>
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
                    <td><?= htmlspecialchars($fila['numero_factura']) ?></td>
                    <td><?= $fila['fecha'] ?></td>
                    <td><?= number_format($fila['monto'], 2) ?></td>
                    <td><?= htmlspecialchars($fila['proveedor']) ?></td>
                    <td>
                        <a href="editar_factura.php?id=<?= $fila['id'] ?>">Editar</a>
                        <a href="eliminar_factura.php?id=<?= $fila['id'] ?>" onclick="return confirm('¿Seguro de eliminar esta factura?')">Eliminar</a>
                    </td>
                </tr>

                <?php
                // Mostrar detalle de productos
                $factura_id = $fila['id'];
                $detalle_sql = "SELECT d.cantidad, d.precio_unitario, p.nombre
                                FROM detalle_factura d
                                INNER JOIN productos p ON d.producto_id = p.id
                                WHERE d.factura_id = $factura_id";
                $detalle_result = $conexion->query($detalle_sql);
                while ($d = $detalle_result->fetch_assoc()):
                ?>
                    <tr class="detalle-factura">
                        <td colspan="2"><?= htmlspecialchars($d['nombre']) ?></td>
                        <td><?= $d['cantidad'] ?> x <?= number_format($d['precio_unitario'],2) ?></td>
                        <td colspan="2">Subtotal: <?= number_format($d['cantidad'] * $d['precio_unitario'],2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

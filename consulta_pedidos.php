<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR puede consultar pedidos
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

// Obtener información del pedido
$stmt = $conexion->prepare("
    SELECT p.id, p.fecha, p.estado, pr.nombre AS proveedor
    FROM pedidos p
    INNER JOIN proveedores pr ON p.proveedor_id = pr.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo "Pedido no encontrado.";
    exit();
}

// Obtener detalles del pedido
$stmtDetalle = $conexion->prepare("
    SELECT dp.cantidad, dp.precio_unitario, pr.nombre AS producto
    FROM detalle_pedidos dp
    INNER JOIN productos pr ON dp.producto_id = pr.id
    WHERE dp.pedido_id = ?
");
$stmtDetalle->bind_param("i", $pedido_id);
$stmtDetalle->execute();
$detalle = $stmtDetalle->get_result();
$stmtDetalle->close();

// Exportar a Excel/CSV si se solicita
if (isset($_GET['export']) && $_GET['export'] == 1) {
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=pedido_{$pedido_id}.csv");

    $output = fopen("php://output", "w");

    // BOM UTF-8 para Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Cabecera
    fputcsv($output, ['Producto', 'Cantidad', 'Precio Unitario', 'Subtotal'], ';');

    $detalle->data_seek(0); // Reiniciar puntero
    $total = 0;
    while ($row = $detalle->fetch_assoc()) {
        $subtotal = $row['cantidad'] * $row['precio_unitario'];
        $total += $subtotal;
        fputcsv($output, [
            $row['producto'],
            $row['cantidad'],
            number_format($row['precio_unitario'], 2, '.', ''),
            number_format($subtotal, 2, '.', '')
        ], ';');
    }

    // Total al final
    fputcsv($output, ['Total', '', '', number_format($total, 2, '.', '')], ';');

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta Pedido</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include("includes/navbar.php"); ?>
<div class="contenido">
    <h2>Detalles del Pedido #<?= $pedido['id'] ?></h2>
    <p><strong>Proveedor:</strong> <?= htmlspecialchars($pedido['proveedor']) ?></p>
    <p><strong>Fecha:</strong> <?= $pedido['fecha'] ?></p>
    <p><strong>Estado:</strong> <?= $pedido['estado'] ?></p>

    <h3>Productos</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Subtotal</th>
        </tr>
        <?php
        $detalle->data_seek(0); // Reiniciar puntero
        $total = 0;
        while ($row = $detalle->fetch_assoc()):
            $subtotal = $row['cantidad'] * $row['precio_unitario'];
            $total += $subtotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($row['producto']) ?></td>
            <td><?= $row['cantidad'] ?></td>
            <td><?= number_format($row['precio_unitario'], 2) ?></td>
            <td><?= number_format($subtotal, 2) ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="3"><strong>Total Pedido:</strong></td>
            <td><strong><?= number_format($total, 2) ?></strong></td>
        </tr>
    </table>
    <br>
    <a href="lista_pedidos.php" class="btn">Volver</a>
</div>
 
</body>
</html>

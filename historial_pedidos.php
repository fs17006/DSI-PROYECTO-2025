<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";

$proveedor_id = (int)($_GET['id'] ?? 0);
if ($proveedor_id <= 0) {
    echo "Proveedor no válido.";
    exit();
}

// Obtener nombre del proveedor
$stmtProv = $conexion->prepare("SELECT nombre FROM proveedores WHERE id = ?");
$stmtProv->bind_param("i", $proveedor_id);
$stmtProv->execute();
$stmtProv->bind_result($nombreProveedor);
$stmtProv->fetch();
$stmtProv->close();

// Obtener pedidos del proveedor
$stmtPedidos = $conexion->prepare("
    SELECT id, fecha, estado
    FROM pedidos
    WHERE proveedor_id = ?
    ORDER BY fecha DESC
");
$stmtPedidos->bind_param("i", $proveedor_id);
$stmtPedidos->execute();
$pedidos = $stmtPedidos->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Pedidos</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include("includes/navbar.php"); ?>
<div class="contenido">
    <h2>Historial de Pedidos - <?= htmlspecialchars($nombreProveedor) ?></h2>

    <?php if ($pedidos->num_rows > 0): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID Pedido</th>
                <th>Fecha</th>
                <th>Estado</th>
            </tr>
            <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                <tr>
                    <td><?= $pedido['id'] ?></td>
                    <td><?= $pedido['fecha'] ?></td>
                    <td><?= $pedido['estado'] ?></td>
                    
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No hay pedidos registrados para este proveedor.</p>
    <?php endif; ?>
    <br>
    <a href="lista_pedidos.php" class="btn">Volver</a>
</div>
</body>
</html>

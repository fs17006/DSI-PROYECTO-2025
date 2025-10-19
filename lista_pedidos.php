<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR puede gestionar pedidos
if ($_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";

// Marcar pedido como completado
if (isset($_GET['completar_id'])) {
    $pedido_id = (int)$_GET['completar_id'];
    $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'COMPLETADO' WHERE id = ?");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $stmt->close();
    header("Location: lista_pedidos.php");
    exit();
}

// Obtener lista de pedidos
$sql = "SELECT p.id, p.fecha, p.estado, p.proveedor_id, prov.nombre AS proveedor
        FROM pedidos p
        INNER JOIN proveedores prov ON p.proveedor_id = prov.id
        ORDER BY p.fecha DESC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Pedidos</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    <div class="contenido">
        <h2>Lista de Pedidos</h2>
        <a href="agregar_pedido.php" class="btn">Nuevo Pedido</a>
        <a href="exportar_pedidos.php" class="btn">Filtrar</a>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID Pedido</th>
                <th>Proveedor</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            <?php while($pedido = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $pedido['id'] ?></td>
                    <td><?= htmlspecialchars($pedido['proveedor']) ?></td>
                    <td><?= $pedido['fecha'] ?></td>
                    <td><?= $pedido['estado'] ?></td>
                    <td>
                        <?php if ($pedido['estado'] === 'PENDIENTE'): ?>
                            <a href="lista_pedidos.php?completar_id=<?= $pedido['id'] ?>" onclick="return confirm('¿Marcar este pedido como completado?');">Completar</a>
                        <?php endif; ?>
                        <a href="consulta_pedidos.php?id=<?= $pedido['id'] ?>">Ver Detalle</a>
                        <a href="historial_pedidos.php?id=<?= $pedido['proveedor_id'] ?>" class="btn">Historial por Proveedor</a>
                        <a href="eliminar_pedido.php?id=<?= $fila['id'] ?>" onclick="return confirm('¿Seguro de eliminar este pedido?')">Eliminar</a>

                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

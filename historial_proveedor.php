<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";

$proveedor_id = $_GET['id'] ?? 0;

// Obtener nombre del proveedor
$stmt = $conexion->prepare("SELECT nombre FROM proveedores WHERE id=?");
$stmt->bind_param("i", $proveedor_id);
$stmt->execute();
$stmt->bind_result($nombre);
$stmt->fetch();
$stmt->close();

// Obtener historial
$sqlHist = "SELECT h.fecha, h.campo, h.valor_anterior, h.valor_nuevo, u.usuario
            FROM historial_proveedores h
            INNER JOIN usuarios u ON h.usuario_id = u.id
            WHERE h.proveedor_id = ?
            ORDER BY h.fecha DESC";

$stmtHist = $conexion->prepare($sqlHist);
$stmtHist->bind_param("i", $proveedor_id);
$stmtHist->execute();
$resultado = $stmtHist->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Proveedor</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    <div class="contenido">
        <h2>Historial de Cambios: <?= htmlspecialchars($nombre) ?></h2>

        <?php if ($resultado->num_rows > 0): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th>Fecha</th>
                    <th>Campo Modificado</th>
                    <th>Valor Anterior</th>
                    <th>Valor Nuevo</th>
                    <th>Usuario</th>
                </tr>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['fecha'] ?></td>
                        <td><?= htmlspecialchars($row['campo']) ?></td>
                        <td><?= htmlspecialchars($row['valor_anterior']) ?></td>
                        <td><?= htmlspecialchars($row['valor_nuevo']) ?></td>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No hay historial de cambios para este proveedor.</p>
        <?php endif; ?>
        <br>
        <a href="lista_proveedores.php" class="btn">Volver</a>
    </div>
</body>
</html>

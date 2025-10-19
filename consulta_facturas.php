<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";

// Obtener lista de proveedores para el filtro
$proveedores = $conexion->query("SELECT id, nombre FROM proveedores");

// Inicializar variables de filtros
$proveedor_id = $_GET['proveedor_id'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$estado = $_GET['estado'] ?? '';

// Construir la consulta dinámicamente según filtros
$where = [];
$params = [];
$types = '';

if ($proveedor_id !== '') {
    $where[] = 'f.proveedor_id = ?';
    $params[] = $proveedor_id;
    $types .= 'i';
}

if ($fecha_inicio !== '') {
    $where[] = 'f.fecha >= ?';
    $params[] = $fecha_inicio;
    $types .= 's';
}

if ($fecha_fin !== '') {
    $where[] = 'f.fecha <= ?';
    $params[] = $fecha_fin;
    $types .= 's';
}

if ($estado !== '') {
    $where[] = 'f.estado = ?';
    $params[] = $estado;
    $types .= 's';
}

$sql = "SELECT f.id, f.numero_factura, f.fecha, f.monto, f.estado, p.nombre AS proveedor
        FROM facturas f
        INNER JOIN proveedores p ON f.proveedor_id = p.id";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY f.fecha DESC";

$stmt = $conexion->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Exportar a Excel/CSV si se solicita
if (isset($_GET['export']) && $_GET['export'] == 1) {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=facturas.csv");
    $output = fopen("php://output", "w");
    fputcsv($output, ['Número Factura', 'Fecha', 'Monto', 'Estado', 'Proveedor']);
    while ($row = $resultado->fetch_assoc()) {
        fputcsv($output, [$row['numero_factura'], $row['fecha'], $row['monto'], $row['estado'], $row['proveedor']]);
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Facturas</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>

    <div class="contenido">
        <h2>Consulta de Facturas</h2>

        <form method="GET" action="">
            <label>Proveedor:</label>
            <select name="proveedor_id">
                <option value="">Todos</option>
                <?php while ($p = $proveedores->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $proveedor_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Fecha Desde:</label>
            <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">

            <label>Fecha Hasta:</label>
            <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">

            <label>Estado:</label>
            <select name="estado">
                <option value="">Todos</option>
                <option value="EMITIDA" <?= $estado == 'EMITIDA' ? 'selected' : '' ?>>Emitida</option>
                <option value="ANULADA" <?= $estado == 'ANULADA' ? 'selected' : '' ?>>Anulada</option>
                <option value="RECHAZADA" <?= $estado == 'RECHAZADA' ? 'selected' : '' ?>>Rechazada</option>
            </select>

            <button type="submit" class="btn">Filtrar</button>
            <button type="submit" name="export" value="1" class="btn">Exportar Excel</button>
        </form>

        <br>

        <?php if ($resultado->num_rows > 0): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Proveedor</th>
                </tr>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['numero_factura']) ?></td>
                        <td><?= $row['fecha'] ?></td>
                        <td><?= number_format($row['monto'], 2) ?></td>
                        <td><?= $row['estado'] ?></td>
                        <td><?= htmlspecialchars($row['proveedor']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No se encontraron facturas con los criterios seleccionados.</p>
        <?php endif; ?>
    </div>
</body>
</html>

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

// Filtros
$proveedores = $conexion->query("SELECT id, nombre FROM proveedores");

$proveedor_id = $_GET['proveedor_id'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$estado = $_GET['estado'] ?? '';

$where = [];
$params = [];
$types = '';

if ($proveedor_id !== '') {
    $where[] = 'p.proveedor_id = ?';
    $params[] = $proveedor_id;
    $types .= 'i';
}

if ($fecha_inicio !== '') {
    $where[] = 'p.fecha >= ?';
    $params[] = $fecha_inicio;
    $types .= 's';
}

if ($fecha_fin !== '') {
    $where[] = 'p.fecha <= ?';
    $params[] = $fecha_fin;
    $types .= 's';
}

if ($estado !== '') {
    $where[] = 'p.estado = ?';
    $params[] = $estado;
    $types .= 's';
}

$sql = "SELECT p.id, p.fecha, p.estado, pr.nombre AS proveedor
        FROM pedidos p
        INNER JOIN proveedores pr ON p.proveedor_id = pr.id";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY p.fecha DESC";

$stmt = $conexion->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Exportar a Excel/CSV
if (isset($_GET['export']) && $_GET['export'] == 1) {
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=pedidos.csv");

    $output = fopen("php://output", "w");
    // BOM UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Cabecera
    fputcsv($output, ['ID Pedido', 'Fecha', 'Estado', 'Proveedor'], ';');

    while ($row = $resultado->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['fecha'],
            $row['estado'],
            $row['proveedor']
        ], ';');
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Pedidos</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<?php include("includes/navbar.php"); ?>
<div class="contenido">
    <h2>Consulta de Pedidos</h2>

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
            <option value="PENDIENTE" <?= $estado == 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
            <option value="COMPLETADO" <?= $estado == 'COMPLETADO' ? 'selected' : '' ?>>Completado</option>
        </select>

        <button type="submit" class="btn">Filtrar</button>
        <button type="submit" name="export" value="1" class="btn">Exportar Excel</button>
    </form>

    <br>

    <?php if ($resultado->num_rows > 0): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID Pedido</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Proveedor</th>
            </tr>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['fecha'] ?></td>
                    <td><?= $row['estado'] ?></td>
                    <td><?= htmlspecialchars($row['proveedor']) ?></td>
                
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No se encontraron pedidos con los criterios seleccionados.</p>
    <?php endif; ?>
    <br>
    <a href="lista_pedidos.php" class="btn">Volver</a>
</div>
</body>
</html>

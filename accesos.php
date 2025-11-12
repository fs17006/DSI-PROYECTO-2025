<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo administrador
if ($_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";

// Obtener filtros
$filtro_usuario = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';
$filtro_fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';

// Construcción de consulta
$condiciones = [];
if ($filtro_usuario !== "") {
    $condiciones[] = "usuario LIKE '%" . $conexion->real_escape_string($filtro_usuario) . "%'";
}
if ($filtro_fecha !== "") {
    $condiciones[] = "DATE(fecha) = '" . $conexion->real_escape_string($filtro_fecha) . "'";
}

$sql = "SELECT * FROM historial_accesos";
if (!empty($condiciones)) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}
$sql .= " ORDER BY fecha DESC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Accesos</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    
    <div class="contenido">
        <h2>Historial de Accesos</h2>

        <!-- ✅ Filtros -->
        <form method="GET">
            <label>Usuario:</label>
            <input type="text" name="usuario" value="<?= htmlspecialchars($filtro_usuario) ?>">

            <label>Fecha:</label>
            <input type="date" name="fecha" value="<?= htmlspecialchars($filtro_fecha) ?>">

            <button type="submit" class="btn">Filtrar</button>
            <a href="accesos.php">Limpiar</a>
        </form>
        <br>

        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Fecha y Hora</th>
                <th>Dirección IP</th>
            </tr>
            <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id']) ?></td>
                    <td><?= htmlspecialchars($fila['usuario']) ?></td>
                    <td><?= htmlspecialchars($fila['fecha']) ?></td>
                    <td><?= htmlspecialchars($fila['ip']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <br>
        
    </div>
</body>
</html>

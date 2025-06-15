<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['perfil'] !== 'ESTANDAR' && $_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}

$conexion = new mysqli("localhost", "root", "", "sistema_web");

$estadoFiltro = isset($_GET['estado']) ? $_GET['estado'] : '';

$sql = "
    SELECT p.id, f.numero_factura, p.fecha_pago, p.monto_pagado, p.estado
    FROM pagos p
    INNER JOIN facturas f ON p.factura_id = f.id
";

if ($estadoFiltro !== '') {
    $estadoFiltro = $conexion->real_escape_string($estadoFiltro);
    $sql .= " WHERE p.estado = '$estadoFiltro'";
}

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Estado de Pagos</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>

    <div class="contenido">
        <h2>Estado de Pagos</h2>

        <form method="GET" action="" class="form-inline">
            <label for="estado">Filtrar por estado:</label>
            <select name="estado" id="estado" class="input-select">
                <option value="">-- Todos --</option>
                <option value="PAGADO" <?= $estadoFiltro === 'PAGADO' ? 'selected' : '' ?>>PAGADO</option>
                <option value="PENDIENTE" <?= $estadoFiltro === 'PENDIENTE' ? 'selected' : '' ?>>PENDIENTE</option>
            </select>
            <button type="submit" class="btn">Buscar</button>
        </form>

        <br>

        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Número de Factura</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Estado de Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['numero_factura']) ?></td>
                    <td><?= htmlspecialchars($fila['fecha_pago']) ?></td>
                    <td><?= number_format($fila['monto_pagado'], 2) ?></td>
                    <td><?= htmlspecialchars($fila['estado']) ?></td>
                    <td>
                        <a href="editar_pago.php?id=<?= $fila['id'] ?>" class="btn-editar">Editar</a>
                        <a href="eliminar_pago.php?id=<?= $fila['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que desea eliminar este pago?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <br>
    </div>
</body>
</html>

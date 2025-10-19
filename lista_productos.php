<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";
$resultado = $conexion->query("SELECT * FROM productos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    <div class="contenido">
        <h2>Lista de Productos</h2>
        <a href="agregar_producto.php" class="btn">Agregar Producto</a>

        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Precio Unitario ($)</th>
                <th>Acciones</th>
            </tr>
            <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['codigo']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre']) ?></td>
                    <td><?= htmlspecialchars(number_format($fila['precio_unitario'], 2)) ?></td>
                    <td>
                        <a href="editar_producto.php?id=<?= $fila['id'] ?>">Editar</a>
                        <a href="eliminar_producto.php?id=<?= $fila['id'] ?>"
                           onclick="return confirm('¿Está seguro que desea eliminar este producto? Esta acción no se puede deshacer.');">
                           Eliminar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

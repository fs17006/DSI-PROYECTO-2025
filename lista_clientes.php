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
$resultado = $conexion->query("SELECT * FROM clientes");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    <div class="contenido">
        <h2>Lista de Clientes</h2>
        <a href="agregar_cliente.php">Agregar Cliente</a>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Nombre</th>
                <th>DUI</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
            <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['nombre']) ?></td>
                    <td><?= htmlspecialchars($fila['dui']) ?></td>
                    <td><?= htmlspecialchars($fila['telefono']) ?></td>
                    <td><?= htmlspecialchars($fila['correo']) ?></td>
                    <td><?= htmlspecialchars($fila['direccion']) ?></td>
                    <td>
                        <a href="editar_cliente.php?id=<?= $fila['id'] ?>">Editar</a>
                        <?php if ($_SESSION['perfil'] === 'ADMINISTRADOR'): ?>
                            <a href="eliminar_cliente.php?id=<?= $fila['id'] ?>" onclick="return confirm('¿Está seguro que desea eliminar este cliente? Esta acción no se puede deshacer.');">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

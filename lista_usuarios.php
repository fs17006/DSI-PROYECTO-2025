<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'ESTANDAR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";
$resultado = $conexion->query("SELECT * FROM usuarios");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    <div class="contenido">
        <h2>Lista de Usuarios</h2>
        <a href="agregar_usuario.php">Agregar usuario</a>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Nombre de usaurio</th>
                <th>clave</th>
                <th>Perfil</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $fila['usuario'] ?></td>
                    <td><?= $fila['contrasena'] ?></td>
                    <td><?= $fila['perfil'] ?></td>
                    <td><?= $fila['activo'] ? 'Activo' : 'No Activo' ?></td>
                    <td>
                        <a href="editar_usuario.php?id=<?= $fila['id'] ?>">Editar</a>
                        <?php if ($_SESSION['perfil'] === 'ADMINISTRADOR'): ?>
                            <a href="eliminar_usuario.php?id=<?= $fila['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

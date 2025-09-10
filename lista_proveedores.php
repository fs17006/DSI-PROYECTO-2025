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

$conexion = new mysqli("sql305.infinityfree.com","if0_39903381", "fYzFQCTA5mOH8","if0_39903381_sistema_web");
$resultado = $conexion->query("SELECT * FROM proveedores");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Proveedores</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    <div class="contenido">
        <h2>Lista de Proveedores</h2>
        <a href="agregar_proveedor.php">Agregar Proveedor</a>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Nombre</th>
                <th>Código</th>
                <th>Actividad Económica</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Acciones</th>
            </tr>
            <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $fila['nombre'] ?></td>
                    <td><?= $fila['codigo'] ?></td>
                    <td><?= $fila['actividad_economica'] ?></td>
                    <td><?= $fila['telefono'] ?></td>
                    <td><?= $fila['correo'] ?></td>
                    <td>
                        <a href="editar_proveedor.php?id=<?= $fila['id'] ?>">Editar</a>
                        <?php if ($_SESSION['perfil'] === 'ADMINISTRADOR'): ?>
                            <a href="eliminar_proveedor.php?id=<?= $fila['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

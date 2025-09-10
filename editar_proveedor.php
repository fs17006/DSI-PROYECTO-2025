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

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: lista_proveedores.php");
    exit();
}

require "conexion.php";
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    $actividad = $_POST['actividad_economica'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';

    $stmt = $conexion->prepare("UPDATE proveedores SET nombre=?, codigo=?, actividad_economica=?, telefono=?, correo=? WHERE id=?");
    $stmt->bind_param("sssssi", $nombre, $codigo, $actividad, $telefono, $correo, $id);
    $stmt->execute();

    if ($stmt->affected_rows >= 0) {
        header("Location: lista_proveedores.php");
        exit();
    } else {
        echo "Error al actualizar proveedor.";
    }

    $stmt->close();
} else {
    $stmt = $conexion->prepare("SELECT nombre, codigo, actividad_economica, telefono, correo FROM proveedores WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($nombre, $codigo, $actividad, $telefono, $correo);
    $stmt->fetch();
    $stmt->close();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Proveedor</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="contenido">
        <h2>Editar Proveedor</h2>
        <form method="POST" class="formulario">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

            <label for="codigo">Código:</label>
            <input type="text" name="codigo" value="<?= htmlspecialchars($codigo) ?>" required>

            <label for="actividad_economica">Actividad Económica:</label>
            <input type="text" name="actividad_economica" value="<?= htmlspecialchars($actividad) ?>" required>

            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($telefono) ?>" required>

            <label for="correo">Correo:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($correo) ?>" required>

            <button type="submit" class="btn">Guardar Cambios</button>
			<button type="submit" href="lista_proveedores.php" class="btn">Volver</button>
        </form>
    </div>
</body>
</html>

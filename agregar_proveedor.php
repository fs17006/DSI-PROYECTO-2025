<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR y ESTANDAR pueden agregar
if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'ESTANDAR') {
    echo "Acceso denegado.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    $actividad = $_POST['actividad_economica'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $correo = $_POST['correo'] ?? '';

require "conexion.php";

    // Usa sentencia preparada para evitar inyección SQL
    $stmt = $conexion->prepare("INSERT INTO proveedores (nombre, codigo, actividad_economica, telefono, correo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $codigo, $actividad, $telefono, $correo);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: lista_proveedores.php");
        exit();
    } else {
        echo "Error al agregar proveedor.";
    }

    $stmt->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
	<link rel="stylesheet" href="css/estilos.css">
</head>
<body>
       <?php include("includes/navbar.php"); ?>
    <div class="contenido">
	 <h2>Agregar Proveedor</h2>
        <form action="agregar_proveedor.php" method="POST" class="formulario">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" required>

            <label for="codigo">Código:</label>
            <input type="text" name="codigo" required>

            <label for="actividad_economica">Actividad Económica:</label>
            <input type="text" name="actividad_economica" required>

            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" required>

            <label for="correo">Correo:</label>
            <input type="email" name="correo" required>

            <button type="submit" class="btn">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>

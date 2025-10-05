<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: plantilla.php');
    exit();
}
require "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Usa sentencia preparada para evitar inyección SQL
    $stmt = $conexion->prepare("INSERT INTO usuarios (usuario, contrasena) VALUES (?,?)");
     $stmt->bind_param("ss", $usuario, $contrasena);
    $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Usuario agregado correctamente.'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Error al agregar usuario.'); window.location.href='agregar_usuario.php';</script>";
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
    <title>Agregar Usuario</title>
</head>
<body>
    <div class="contenido">
        <h2>Agregar Usuario</h2>
        <form action="agregar_usuario.php" method="POST" class="formulario">
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" required>

            <label for="contrasena">Contraseña:</label>
            <input type="text" name="contrasena" required>

            <button type="submit" class="btn">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>

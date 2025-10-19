<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: plantilla.php');
    exit();
}

require "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = $_POST['nombre_completo'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Encriptar la contraseña antes de guardarla
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Sentencia preparada para evitar inyección SQL
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo, usuario, contrasena) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre_completo, $correo, $usuario, $hash);

    if ($stmt->execute()) {
        echo "<script>alert('Usuario agregado correctamente. Ahora puede iniciar sesión.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error al agregar usuario. Puede que el usuario o correo ya existan.'); window.location.href='agregar_usuario.php';</script>";
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
        <h2>Crear Cuenta</h2>
        <form action="agregar_usuario.php" method="POST" class="formulario">
            <label for="nombre_completo">Nombre completo:</label>
            <input type="text" name="nombre_completo" required>

            <label for="correo">Correo electrónico:</label>
            <input type="email" name="correo" required>

            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" required>

            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" required>

            <button type="submit" class="btn">Registrar Usuario</button>
        </form>
    </div>
</body>
</html>

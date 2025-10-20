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
    <style>
        .required::after { content: " *"; color: red; }
        .error-message { color: red; display: none; margin-bottom: 10px; }
        input:invalid { border-color: red; }
    </style>
</head>
<body>
<div class="contenido">
    <h2>Crear Cuenta</h2>

    <div class="error-message" id="errorMsg">Por favor complete todos los campos obligatorios.</div>

    <form action="agregar_usuario.php" method="POST" class="formulario" id="formUsuario">
        <label for="nombre_completo" class="required">Nombre completo:</label>
        <input type="text" name="nombre_completo" required>

        <label for="correo" class="required">Correo electrónico:</label>
        <input type="email" name="correo" required>

        <label for="usuario" class="required">Usuario:</label>
        <input type="text" name="usuario" required>

        <label for="contrasena" class="required">Contraseña:</label>
        <input type="password" name="contrasena" required>
        <button type="submit" class="btn">Registrar Usuario</button>
        <a href="index.php" class="btn">Volver</a>
    </form>
    
</div>

<script>
    const form = document.getElementById('formUsuario');
    const errorMsg = document.getElementById('errorMsg');

    form.addEventListener('submit', function(e) {
        let allFilled = true;
        form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value.trim()) {
                allFilled = false;
            }
        });

        if (!allFilled) {
            e.preventDefault(); // Evita enviar formulario
            errorMsg.style.display = 'block'; // Muestra mensaje
        } else {
            errorMsg.style.display = 'none';
        }
    });
</script>
</body>
</html>

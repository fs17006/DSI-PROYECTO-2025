<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: plantilla.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <link rel="stylesheet" href="css/estilos.css" />
</head>
<body>
    <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <form action="procesar_login.php" method="POST" autocomplete="off">
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" id="usuario" required autofocus />
            
            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena" required />
            
            <input type="submit" value="Entrar" />
        </form>
        <form action="agregar_usuario.php" method="GET" style="margin-top: 15px;">
    <input type="submit" value="Crear Usuario" />
    </form>
    </div>
</body>
</html>

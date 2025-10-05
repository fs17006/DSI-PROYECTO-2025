<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

if ($_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include('includes/navbar.php'); ?>
    <div class="contenido">
        <h2>Bienvenido al sistema.</h2>
    </div>
</body>
</html>

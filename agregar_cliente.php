<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $dui = trim($_POST['dui']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $direccion = trim($_POST['direccion']);

    if (empty($nombre) || empty($dui) || empty($correo)) {
        echo "<script>alert('Los campos Nombre, DUI y Correo son obligatorios.');</script>";
    } else {
        // Validar duplicados
        $stmt = $conexion->prepare("SELECT * FROM clientes WHERE dui = ? OR correo = ?");
        $stmt->bind_param("ss", $dui, $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            echo "<script>alert('El DUI o el correo ya están registrados.');</script>";
        } else {
            $insert = $conexion->prepare("INSERT INTO clientes (nombre, dui, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $nombre, $dui, $telefono, $correo, $direccion);
            if ($insert->execute()) {
                echo "<script>alert('Cliente registrado exitosamente.'); window.location.href='lista_clientes.php';</script>";
            } else {
                echo "<script>alert('Error al registrar el cliente.');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Cliente</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .required::after { content: " *"; color: red; }
        .error-message { color: red; display: none; margin-bottom: 10px; }
        input:invalid { border-color: red; }
    </style>
</head>
<body>
<?php include("includes/navbar.php"); ?>
<div class="contenido">
    <h2>Registrar Cliente</h2>

    <div class="error-message" id="errorMsg">Por favor complete todos los campos obligatorios.</div>

    <form method="POST" class="formulario" id="formCliente">
        <label class="required">Nombre:</label>
        <input type="text" name="nombre" required>

        <label class="required">DUI:</label>
        <input type="text" name="dui" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono">

        <label class="required">Correo:</label>
        <input type="email" name="correo" required>

        <label>Dirección:</label>
        <input type="text" name="direccion">

        <button type="submit" class="btn">Guardar</button>
        <a href="lista_clientes.php" class="btn">Volver</a>
    </form>
</div>

<script>
    const form = document.getElementById('formCliente');
    const errorMsg = document.getElementById('errorMsg');

    form.addEventListener('submit', function(e) {
        let allFilled = true;
        form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value.trim()) {
                allFilled = false;
            }
        });

        if (!allFilled) {
            e.preventDefault();
            errorMsg.style.display = 'block';
        } else {
            errorMsg.style.display = 'none';
        }
    });
</script>
</body>
</html>

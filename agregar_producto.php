<?php
require "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio_unitario']);

    // Validaciones
    if ($precio <= 0) {
        echo "<script>alert('El precio debe ser positivo.'); history.back();</script>";
        exit();
    }

    // Verificar si el código ya existe
    $check = $conexion->prepare("SELECT id FROM productos WHERE codigo = ?");
    $check->bind_param("s", $codigo);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('El código del producto ya existe.'); history.back();</script>";
        exit();
    }

    // Insertar producto
    $stmt = $conexion->prepare("INSERT INTO productos (codigo, nombre, precio_unitario) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $codigo, $nombre, $precio);
    $stmt->execute();

    header("Location: lista_productos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
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
    <h2>Registrar Producto</h2>

    <div class="error-message" id="errorMsg">Por favor complete todos los campos obligatorios.</div>

    <form action="" method="POST" class="formulario" id="formProducto">
        <label for="codigo" class="required">Código:</label>
        <input type="text" name="codigo" required>

        <label for="nombre" class="required">Nombre:</label>
        <input type="text" name="nombre" required>

        <label for="precio_unitario" class="required">Precio unitario:</label>
        <input type="number" name="precio_unitario" step="0.01" min="0.01" required>

        <button type="submit" class="btn">Registrar Producto</button>
        <a href="lista_productos.php" class="btn">Volver</a>
    </form>
</div>

<script>
    const form = document.getElementById('formProducto');
    const errorMsg = document.getElementById('errorMsg');

    form.addEventListener('submit', function(e) {
        let allFilled = true;
        form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value.trim()) {
                allFilled = false;
            }
        });

        if (!allFilled) {
            e.preventDefault(); // evita enviar formulario
            errorMsg.style.display = 'block'; // muestra mensaje
        } else {
            errorMsg.style.display = 'none';
        }
    });
</script>
</body>
</html>

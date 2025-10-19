<?php
require "conexion.php";
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: lista_productos.php");
    exit();
}

// Obtener datos actuales del producto
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

if (!$producto) {
    echo "<script>alert('Producto no encontrado.'); window.location='lista_productos.php';</script>";
    exit();
}

// Si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $nuevo_precio = floatval($_POST['precio_unitario']);
    $precio_anterior = floatval($producto['precio_unitario']);

    if ($nuevo_precio <= 0) {
        echo "<script>alert('El precio debe ser positivo.'); history.back();</script>";
        exit();
    }

    // Registrar en historial de precios si el precio cambió
    if ($nuevo_precio != $precio_anterior) {
        $historial = $conexion->prepare("INSERT INTO historial_precios (producto_id, precio_anterior, precio_nuevo)
                                         VALUES (?, ?, ?)");
        $historial->bind_param("idd", $id, $precio_anterior, $nuevo_precio);
        $historial->execute();
    }

    // Actualizar producto
    $stmt = $conexion->prepare("UPDATE productos SET nombre = ?, precio_unitario = ? WHERE id = ?");
    $stmt->bind_param("sdi", $nombre, $nuevo_precio, $id);
    $stmt->execute();

    header("Location: lista_productos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="contenido">
        <h2>Editar Producto</h2>
        <form action="" method="POST" class="formulario">
            <label for="codigo">Código:</label>
            <input type="text" name="codigo" value="<?= htmlspecialchars($producto['codigo']) ?>" readonly>
            <small style="color:red;">El código del producto no puede ser modificado.</small>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>

            <label for="precio_unitario">Precio unitario:</label>
            <input type="number" name="precio_unitario" step="0.01" min="0.01" 
                   value="<?= htmlspecialchars($producto['precio_unitario']) ?>" required>

            <button type="submit" class="btn">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>

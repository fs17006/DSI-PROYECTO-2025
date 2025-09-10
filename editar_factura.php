<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $resultado = $conexion->query("SELECT * FROM facturas WHERE id = $id");
    $factura = $resultado->fetch_assoc();

    $proveedores = $conexion->query("SELECT id, nombre FROM proveedores");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $numero = $_POST['numero_factura'];
    $fecha = $_POST['fecha'];
    $monto = $_POST['monto'];
    $proveedor_id = $_POST['proveedor_id'];

    $conexion->query("UPDATE facturas SET numero_factura='$numero', fecha='$fecha', monto='$monto', proveedor_id='$proveedor_id' WHERE id=$id");

    header("Location: lista_facturas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Factura</title>
    <link rel="stylesheet" href="css/estilos.css"> <!-- Asegúrate de tener este archivo -->
</head>
<body>
    <?php include("includes/navbar.php"); ?>

    <div class="contenido">
        <h2>Editar Factura</h2>
        <form method="POST" class="formulario">
            <input type="hidden" name="id" value="<?= $factura['id'] ?>">

            <label>Número de Factura:</label>
            <input type="text" name="numero_factura" value="<?= $factura['numero_factura'] ?>" required><br>

            <label>Fecha:</label>
            <input type="date" name="fecha" value="<?= $factura['fecha'] ?>" required><br>

            <label>Monto:</label>
            <input type="number" step="0.01" name="monto" value="<?= $factura['monto'] ?>" required><br>

            <label>Proveedor:</label>
            <select name="proveedor_id" required>
                <?php while ($p = $proveedores->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $factura['proveedor_id'] ? 'selected' : '' ?>>
                        <?= $p['nombre'] ?>
                    </option>
                <?php endwhile; ?>
            </select><br>

       
			<button type="submit" class="btn">Guardar Cambios</button>
			<button type="submit" href="lista_facturas.php" class="btn">Volver</button>
        </form>
    </div>
</body>
</html>

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'ESTANDAR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";

$proveedores = $conexion->query("SELECT id, nombre FROM proveedores");
$productos = $conexion->query("SELECT id, nombre, precio_unitario FROM productos");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numero = $_POST['numero_factura'];
    $fecha = $_POST['fecha'];
    $monto = $_POST['monto'];
    $proveedor_id = $_POST['proveedor_id'];

  $total_monto = 0;
if (!empty($_POST['producto_id'])) {
    foreach ($_POST['producto_id'] as $index => $producto_id) {
        $cantidad = $_POST['cantidad'][$index];
        $precio = $_POST['precio_unitario'][$index];
        $total_monto += $cantidad * $precio;
    }
}

// Insertar factura con monto calculado
$stmt = $conexion->prepare("INSERT INTO facturas (numero_factura, fecha, monto, proveedor_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssdi", $numero, $fecha, $total_monto, $proveedor_id);
$stmt->execute();

    $factura_id = $stmt->insert_id;

    // Insertar detalles
    if (!empty($_POST['producto_id'])) {
        foreach ($_POST['producto_id'] as $index => $producto_id) {
            $cantidad = $_POST['cantidad'][$index];
            $precio = $_POST['precio_unitario'][$index];

            $detalle = $conexion->prepare("INSERT INTO detalle_factura (factura_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $detalle->bind_param("iiid", $factura_id, $producto_id, $cantidad, $precio);
            $detalle->execute();
        }
    }

    header("Location: lista_facturas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/estilos.css">
    <title>Agregar Factura con Detalle</title>
    <style>
        .detalle-row { display: flex; gap: 10px; margin-bottom: 5px; }
        .detalle-row select, .detalle-row input { flex: 1; }
        .btn-add { margin-top: 10px; }
    </style>
</head>
<body>
    <?php include("includes/navbar.php"); ?>

    <h2>Agregar Factura</h2>
<form method="POST" action="" id="formFactura">
    <label>Número de Factura:</label>
    <input type="text" name="numero_factura" required>

    <label>Fecha:</label>
    <input type="date" name="fecha" required>

    <label>Proveedor:</label>
    <select name="proveedor_id" required>
        <?php while ($p = $proveedores->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
        <?php endwhile; ?>
    </select>

    <h3>Detalles de la Factura</h3>
    <div id="detalle-container">
        <div class="detalle-row">
            <select name="producto_id[]" class="producto-select" onchange="actualizarPrecio(this)">
                <?php
                $productos->data_seek(0); // reinicia el puntero
                while ($prod = $productos->fetch_assoc()):
                ?>
                    <option value="<?= $prod['id'] ?>" data-precio="<?= $prod['precio_unitario'] ?>">
                        <?= htmlspecialchars($prod['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="cantidad[]" placeholder="Cantidad" min="1" value="1" oninput="calcularTotal()" required>
            <input type="number" step="0.01" name="precio_unitario[]" placeholder="Precio Unitario" readonly>
        </div>
    </div>

    <button type="button" class="btn-add" onclick="agregarDetalle()">+ Agregar Producto</button>
    <br><br>
    <label>Total Factura:</label>
    <input type="number" id="monto_total" step="0.01" readonly>
    <input type="hidden" name="monto" id="monto_oculto">
    <br><br>
    <button type="submit" class="btn">Guardar Factura</button>
</form>

<script>
    function actualizarPrecio(select) {
        const precio = select.selectedOptions[0].dataset.precio;
        const row = select.parentElement;
        row.querySelector('input[name="precio_unitario[]"]').value = precio;
        calcularTotal();
    }

    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('.detalle-row').forEach(row => {
            const cantidad = parseFloat(row.querySelector('input[name="cantidad[]"]').value) || 0;
            const precio = parseFloat(row.querySelector('input[name="precio_unitario[]"]').value) || 0;
            total += cantidad * precio;
        });
        document.getElementById('monto_total').value = total.toFixed(2);
        document.getElementById('monto_oculto').value = total.toFixed(2);
    }

    function agregarDetalle() {
        const cont = document.getElementById("detalle-container");
        const row = document.querySelector(".detalle-row").cloneNode(true);
        row.querySelectorAll("input").forEach(i => i.value = i.name === 'cantidad[]' ? 1 : 0);
        // resetear select al primero
        const select = row.querySelector('select');
        select.selectedIndex = 0;
        actualizarPrecio(select); // actualizar precio del nuevo select
        cont.appendChild(row);
    }

    // Inicializar precio del primer producto
    document.querySelectorAll('.producto-select').forEach(select => actualizarPrecio(select));
</script>

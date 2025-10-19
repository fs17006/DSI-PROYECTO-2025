<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";

// Obtener factura y proveedores
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $facturaRes = $conexion->query("SELECT * FROM facturas WHERE id = $id");
    $factura = $facturaRes->fetch_assoc();

    $proveedores = $conexion->query("SELECT id, nombre FROM proveedores");

    // Obtener detalle de factura
    $detalleRes = $conexion->query("SELECT d.*, p.nombre 
                                    FROM detalle_factura d 
                                    INNER JOIN productos p ON d.producto_id = p.id 
                                    WHERE d.factura_id = $id");
    $detalle = [];
    while ($row = $detalleRes->fetch_assoc()) {
        $detalle[] = $row;
    }

    // Obtener lista de todos los productos
    $productosRes = $conexion->query("SELECT * FROM productos WHERE activo = 1");
}

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $numero = $_POST['numero_factura'];
    $fecha = $_POST['fecha'];
    $proveedor_id = $_POST['proveedor_id'];

    // Calcular total automáticamente
    $total_monto = 0;
    if (!empty($_POST['producto_id'])) {
        foreach ($_POST['producto_id'] as $index => $producto_id) {
            $cantidad = $_POST['cantidad'][$index];
            $precio = $_POST['precio_unitario'][$index];
            $total_monto += $cantidad * $precio;
        }
    }

    // Actualizar tabla facturas
    $stmt = $conexion->prepare("UPDATE facturas SET numero_factura=?, fecha=?, monto=?, proveedor_id=? WHERE id=?");
    $stmt->bind_param("ssdii", $numero, $fecha, $total_monto, $proveedor_id, $id);
    $stmt->execute();

    // Borrar detalle anterior
    $conexion->query("DELETE FROM detalle_factura WHERE factura_id = $id");

    // Insertar nuevo detalle
    if (!empty($_POST['producto_id'])) {
        foreach ($_POST['producto_id'] as $index => $producto_id) {
            $cantidad = $_POST['cantidad'][$index];
            $precio = $_POST['precio_unitario'][$index];

            $stmtDet = $conexion->prepare("INSERT INTO detalle_factura (factura_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmtDet->bind_param("iiid", $id, $producto_id, $cantidad, $precio);
            $stmtDet->execute();
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
    <title>Editar Factura</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .detalle-row { display: flex; gap: 10px; margin-bottom: 5px; }
        .detalle-row select, .detalle-row input { flex: 1; }
        .btn-add { margin-top: 10px; }
    </style>
</head>
<body>
<?php include("includes/navbar.php"); ?>

<div class="contenido">
    <h2>Editar Factura</h2>
    <form method="POST" id="formFactura">
        <input type="hidden" name="id" value="<?= $factura['id'] ?>">

        <label>Número de Factura:</label>
        <input type="text" name="numero_factura" value="<?= htmlspecialchars($factura['numero_factura']) ?>" required><br>

        <label>Fecha:</label>
        <input type="date" name="fecha" value="<?= $factura['fecha'] ?>" required><br>

        <label>Proveedor:</label>
        <select name="proveedor_id" required>
            <?php while ($p = $proveedores->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $factura['proveedor_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select><br>

        <h3>Detalles de la Factura</h3>
        <div id="detalle-container">
            <?php foreach ($detalle as $d): ?>
                <div class="detalle-row">
                    <select name="producto_id[]" class="producto-select" onchange="actualizarPrecio(this)">
                        <?php
                        $productosRes->data_seek(0); // reiniciar puntero
                        while ($prod = $productosRes->fetch_assoc()):
                        ?>
                            <option value="<?= $prod['id'] ?>" data-precio="<?= $prod['precio_unitario'] ?>" <?= $prod['id'] == $d['producto_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prod['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <input type="number" name="cantidad[]" value="<?= $d['cantidad'] ?>" min="1" oninput="calcularTotal()" required>
                    <input type="number" step="0.01" name="precio_unitario[]" value="<?= $d['precio_unitario'] ?>" readonly>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn-add" onclick="agregarDetalle()">+ Agregar Producto</button><br><br>

        <label>Total Factura:</label>
        <input type="number" id="monto_total" step="0.01" readonly>
        <input type="hidden" name="monto" id="monto_oculto"><br><br>

        <button type="submit" class="btn">Guardar Cambios</button>
        <a href="lista_facturas.php" class="btn">Volver</a>
    </form>
</div>

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
        const select = row.querySelector('select');
        select.selectedIndex = 0;
        actualizarPrecio(select);
        cont.appendChild(row);
    }

    // Inicializar precios y total al cargar la página
    document.querySelectorAll('.producto-select').forEach(select => actualizarPrecio(select));
</script>
</body>
</html>

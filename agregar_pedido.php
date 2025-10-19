<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR puede agregar pedidos
if ($_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";

// Obtener proveedores y productos
$proveedores = $conexion->query("SELECT id, nombre FROM proveedores ORDER BY nombre");
$productos = $conexion->query("SELECT id, nombre, precio_unitario FROM productos WHERE activo = 1 ORDER BY nombre");

$mensaje = "";

// Guardar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proveedor_id = (int)($_POST['proveedor_id'] ?? 0);
    $fecha = date('Y-m-d H:i:s');
    $productos_ids = $_POST['producto_id'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    $precios = $_POST['precio_unitario'] ?? [];

    if ($proveedor_id <= 0 || empty($productos_ids)) {
        $mensaje = "Seleccione un proveedor y al menos un producto.";
    } else {
        // Insertar pedido
        $stmt = $conexion->prepare("INSERT INTO pedidos (proveedor_id, fecha, estado) VALUES (?, ?, 'PENDIENTE')");
        $stmt->bind_param("is", $proveedor_id, $fecha);

        $stmt->execute();
        $pedido_id = $stmt->insert_id;
        $stmt->close();

        // Insertar detalle del pedido
        $stmtDetalle = $conexion->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        foreach ($productos_ids as $index => $prod_id) {
            $cantidad = (int)$cantidades[$index];
            $precio = (float)$precios[$index];
            $stmtDetalle->bind_param("iiid", $pedido_id, $prod_id, $cantidad, $precio);
            $stmtDetalle->execute();
        }
        $stmtDetalle->close();

        header("Location: lista_pedidos.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Pedido</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .detalle-row { display: flex; gap: 10px; margin-bottom: 5px; }
        .detalle-row select, .detalle-row input { flex: 1; }
        .btn-add { margin-top: 10px; }
        .error-message { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>
<?php include("includes/navbar.php"); ?>
<div class="contenido">
    <h2>Agregar Pedido</h2>

    <?php if ($mensaje): ?>
        <div class="error-message"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="POST" id="formPedido">
        <label>Proveedor:</label>
        <select name="proveedor_id" required>
            <option value="">-- Seleccione un proveedor --</option>
            <?php while ($p = $proveedores->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Fecha:</label>
        <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>

        <h3>Productos</h3>
        <div id="detalle-container">
            <div class="detalle-row">
                <select name="producto_id[]" class="producto-select" onchange="actualizarPrecio(this)" required>
                    <option value="">-- Seleccione--</option>
                    <?php
                    $productos->data_seek(0);
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

        <button type="button" class="btn btn-add" onclick="agregarDetalle()">+ Agregar Producto</button>
        <br><br>

        <label>Total Pedido:</label>
        <input type="number" id="total_pedido" step="0.01" readonly>
        <input type="hidden" name="monto_total" id="monto_oculto">
        <br><br>

        <button type="submit" class="btn">Guardar Pedido</button>
        <a href="lista_pedidos.php" class="btn">Volver</a>
    </form>
</div>

<script>
function actualizarPrecio(select) {
    const precioInput = select.parentElement.querySelector('input[name="precio_unitario[]"]');
    const selectedOption = select.options[select.selectedIndex];
    precioInput.value = selectedOption.dataset.precio || 0;
    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    document.querySelectorAll('#detalle-container .detalle-row').forEach(row => {
        const cantidad = parseFloat(row.querySelector('input[name="cantidad[]"]').value) || 0;
        const precio = parseFloat(row.querySelector('input[name="precio_unitario[]"]').value) || 0;
        total += cantidad * precio;
    });
    document.getElementById('total_pedido').value = total.toFixed(2);
    document.getElementById('monto_oculto').value = total.toFixed(2);
}

function agregarDetalle() {
    const container = document.getElementById('detalle-container');
    const row = container.firstElementChild.cloneNode(true);
    row.querySelectorAll('input').forEach(input => input.value = input.name.includes('cantidad') ? 1 : '');
    row.querySelector('select').selectedIndex = 0;
    container.appendChild(row);
}
</script>
</body>
</html>

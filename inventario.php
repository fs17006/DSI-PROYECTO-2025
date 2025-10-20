<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";

$mensaje = "";

// --- Procesar ajustes de inventario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = (int)($_POST['producto_id'] ?? 0);
    $ajuste = (int)($_POST['ajuste'] ?? 0);

    if ($producto_id > 0 && $ajuste !== 0) {
        $stmt = $conexion->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $ajuste, $producto_id);
        $stmt->execute();
        $stmt->close();

        $mensaje = "Stock del producto actualizado correctamente";
    } else {
        $mensaje = "Debe ingresar un ajuste válido";
    }
}

// --- Filtros ---
$busqueda = $_GET['busqueda'] ?? '';
$estado = $_GET['estado'] ?? '';

// --- Consulta principal ---
$sql = "SELECT id, codigo, nombre, precio_unitario, stock, activo, fecha_creacion FROM productos WHERE 1=1";
$params = [];
$types = '';

if ($busqueda !== '') {
    $sql .= " AND nombre LIKE ?";
    $params[] = "%$busqueda%";
    $types .= 's';
}

if ($estado !== '') {
    $sql .= " AND activo = ?";
    $params[] = $estado;
    $types .= 'i';
}

$stmt = $conexion->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();

// --- Resumen general ---
$total_prod = $conexion->query("SELECT COUNT(*) AS total FROM productos")->fetch_assoc()['total'];
$total_activos = $conexion->query("SELECT COUNT(*) AS total FROM productos WHERE activo = 1")->fetch_assoc()['total'];
$total_inactivos = $total_prod - $total_activos;
$valor_total = $conexion->query("SELECT SUM(precio_unitario * stock) AS total FROM productos")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control de Inventario</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .resumen {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            flex: 1;
            text-align: center;
        }
        .ajuste-form {
            display: inline-block;
        }
    </style>
</head>
<body>
<?php include("includes/navbar.php"); ?>

<div class="contenido">
    <h2>Panel de Control de Inventario</h2>

    <div class="resumen">
        <div class="card"><strong>Total Productos:</strong><br><?= $total_prod ?></div>
        <div class="card"><strong>Activos:</strong><br><?= $total_activos ?></div>
        <div class="card"><strong>Inactivos:</strong><br><?= $total_inactivos ?></div>
        <div class="card"><strong>Valor Total Inventario:</strong><br>$<?= number_format($valor_total, 2) ?></div>
    </div>

    <form method="GET">
        <label>Buscar por nombre :</label>
        <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>">
        <label>Estado:</label>
        <select name="estado">
            <option value="">Todos</option>
            <option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
            <option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
        </select>
        <button type="submit" class="btn">Filtrar</button>
    </form>

    <br>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Precio Unitario</th>
            <th>Stock</th>
            <th>Estado</th>
            <th>Fecha Creación</th>
            <th>Ajustar</th>
        </tr>
        <?php while ($p = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($p['codigo']) ?></td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td>$<?= number_format($p['precio_unitario'], 2) ?></td>
            <td><?= $p['stock'] ?></td>
            <td><?= $p['activo'] ? 'Activo' : 'Inactivo' ?></td>
            <td><?= $p['fecha_creacion'] ?></td>
            <td>
                <form method="POST" class="ajuste-form">
                    <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                    <input type="number" name="ajuste" step="1" required placeholder="+/-">
                    <button type="submit" class="btn">Aplicar</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php if ($mensaje): ?>
<script>
    alert("<?= addslashes($mensaje) ?>");
    // Recargar la página para reflejar el nuevo stock
    window.location.href = "inventario.php";
</script>
<?php endif; ?>

</body>
</html>

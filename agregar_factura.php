<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR o ESTANDAR pueden agregar facturas
if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'ESTANDAR') {
    echo "Acceso denegado.";
    exit();
}

$conexion = new mysqli("sql305.infinityfree.com","if0_39903381", "fYzFQCTA5mOH8","if0_39903381_sistema_web");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numero = $_POST['numero_factura'];
    $fecha = $_POST['fecha'];
    $monto = $_POST['monto'];
    $proveedor_id = $_POST['proveedor_id'];

    $stmt = $conexion->prepare("INSERT INTO facturas (numero_factura, fecha, monto, proveedor_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $numero, $fecha, $monto, $proveedor_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: lista_facturas.php");
        exit();
    } else {
        echo "Error al agregar la factura.";
    }
}

$proveedores = $conexion->query("SELECT id, nombre FROM proveedores");
?>

<!DOCTYPE html>
<html lang="es">
<link rel="stylesheet" href="css/estilos.css">
<head>
    <meta charset="UTF-8">
    <title>Agregar Factura</title>
</head>
<body>
    <?php include("includes/navbar.php"); ?>

    <h2>Agregar Factura</h2>
    <form method="post" action="">
        <label for="numero_factura">Número de Factura:</label>
        <input type="text" id="numero_factura" name="numero_factura" required>

        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" required>

        <label for="monto">Monto:</label>
        <input type="number" id="monto" name="monto" step="0.01" required>

        <label for="proveedor_id">Proveedor:</label>
        <select id="proveedor_id" name="proveedor_id" required>
            <?php while ($p = $proveedores->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
            <?php endwhile; ?>
        </select>
		<button type="submit" class="btn">Guardar </button>
			
    </form>
</body>
</html>

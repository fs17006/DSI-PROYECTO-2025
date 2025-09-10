<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'CONTADOR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $factura_id = $_POST['factura_id'];
    $fecha_pago = $_POST['fecha_pago'];
    $monto_pagado = $_POST['monto_pagado'];
    $estado = $_POST['estado'];

    $stmt = $conexion->prepare("INSERT INTO pagos (factura_id, fecha_pago, monto_pagado, estado) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $factura_id, $fecha_pago, $monto_pagado, $estado);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
    header("Location: estado_pagos.php?mensaje=registrado");
    exit();
} else {
    echo "Error al registrar el pago.";
}


    $stmt->close();
}

// Obtener facturas pendientes
$facturas = $conexion->query("SELECT id, numero_factura FROM facturas");
?>

<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registrar Pago</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>

    <div class="contenido">
        <h2>Registrar Pago</h2>

        <form method="POST" class="formulario">
            <label for="factura_id">Factura:</label>
            <select name="factura_id" id="factura_id" required class="input-select">
                <?php while ($f = $facturas->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($f['id']) ?>"><?= htmlspecialchars($f['numero_factura']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="fecha_pago">Fecha de Pago:</label>
            <input type="date" name="fecha_pago" id="fecha_pago" required class="input-text">

            <label for="monto_pagado">Monto Pagado:</label>
            <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" required class="input-text">

            <label for="estado">Estado:</label>
            <select name="estado" id="estado" required class="input-select">
                <option value="PAGADO">Pagado</option>
                <option value="PENDIENTE">Pendiente</option>
            </select>

            <button type="submit" class="btn">Registrar Pago</button>
			 
        </form>

       
    </div>
</body>
</html>
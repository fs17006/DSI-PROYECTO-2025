<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR o CONTADOR pueden editar pagos
if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'CONTADOR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Obtener datos actuales del pago
    $query = $conexion->prepare("SELECT * FROM pagos WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 0) {
        echo "Pago no encontrado.";
        exit();
    }

    $pago = $resultado->fetch_assoc();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar la edición del pago
    $id = intval($_POST['id']);
    $estado = $_POST['estado'];
    $fecha_pago = $_POST['fecha_pago'];

    $update = $conexion->prepare("UPDATE pagos SET estado = ?, fecha_pago = ? WHERE id = ?");
    $update->bind_param("ssi", $estado, $fecha_pago, $id);

    if ($update->execute()) {
        header("Location: estado_pagos.php?mensaje=editado");
        exit();
    } else {
        echo "Error al actualizar el pago.";
    }
} else {
    echo "Solicitud no válida.";
    exit();
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Pago</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>

    <div class="contenido">
        <h2>Editar Pago</h2>

        <form method="POST" action="editar_pago.php" class="formulario">
            <input type="hidden" name="id" value="<?= htmlspecialchars($pago['id']) ?>">

            <label for="estado">Estado:</label>
            <select name="estado" id="estado" required class="input-select">
                <option value="PAGADO" <?= $pago['estado'] === 'PAGADO' ? 'selected' : '' ?>>PAGADO</option>
                <option value="PENDIENTE" <?= $pago['estado'] === 'PENDIENTE' ? 'selected' : '' ?>>PENDIENTE</option>
            </select>

            <label for="fecha_pago">Fecha de Pago:</label>
            <input type="date" name="fecha_pago" id="fecha_pago" value="<?= htmlspecialchars($pago['fecha_pago']) ?>" required class="input-text">

            <button type="submit" class="btn">Guardar Cambios</button>
			<button type="submit" href="estado_pagos.php" class="btn">Volver</button>
        </form>

        
    </div>
</body>
</html>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR y ESTANDAR
if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'ESTANDAR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: lista_proveedores.php");
    exit();
}

// Obtener lista de usuarios
$usuariosRes = $conexion->query("SELECT id, nombre_completo, correo FROM usuarios WHERE perfil IN ('ADMINISTRADOR','ESTANDAR')");

// Obtener datos del proveedor
$stmt = $conexion->prepare("SELECT nombre, codigo, actividad_economica, telefono, usuario_id FROM proveedores WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nombre, $codigo, $actividad, $telefono, $usuario_id);
$stmt->fetch();
$stmt->close();

$mensaje = "";

// Obtener datos actuales
$stmtOld = $conexion->prepare("SELECT nombre, codigo, actividad_economica, telefono FROM proveedores WHERE id=?");
$stmtOld->bind_param("i", $id);
$stmtOld->execute();
$stmtOld->bind_result($oldNombre, $oldCodigo, $oldActividad, $oldTelefono);
$stmtOld->fetch();
$stmtOld->close();

// Comparar campo por campo y registrar cambios
$campos = [
    'nombre' => [$oldNombre, $nombre],
    'codigo' => [$oldCodigo, $codigo],
    'actividad_economica' => [$oldActividad, $actividad],
    'telefono' => [$oldTelefono, $telefono]
];

foreach ($campos as $campo => [$antiguo, $nuevo]) {
    if ($antiguo != $nuevo) {
        $stmtHist = $conexion->prepare("INSERT INTO historial_proveedores (proveedor_id, campo, valor_anterior, valor_nuevo, usuario_id) VALUES (?, ?, ?, ?, ?)");
        $stmtHist->bind_param("isssi", $id, $campo, $antiguo, $nuevo, $_SESSION['id']); // $_SESSION['id'] = usuario logueado
        $stmtHist->execute();
        $stmtHist->close();
    }
}

// Luego se actualiza la tabla proveedores
$stmtUpd = $conexion->prepare("UPDATE proveedores SET nombre=?, codigo=?, actividad_economica=?, telefono=? WHERE id=?");
$stmtUpd->bind_param("ssssi", $nombre, $codigo, $actividad, $telefono, $id);
$stmtUpd->execute();

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    $actividad = trim($_POST['actividad_economica'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $usuario_id = (int)($_POST['usuario_id'] ?? 0);

    if (empty($nombre) || empty($codigo) || empty($actividad) || empty($telefono) || $usuario_id <= 0) {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        // Obtener valores antiguos de la base
        $stmtOld = $conexion->prepare("SELECT nombre, codigo, actividad_economica, telefono FROM proveedores WHERE id=?");
        $stmtOld->bind_param("i", $id);
        $stmtOld->execute();
        $stmtOld->bind_result($oldNombre, $oldCodigo, $oldActividad, $oldTelefono);
        $stmtOld->fetch();
        $stmtOld->close();

        // Comparar y registrar cambios
        $campos = [
            'nombre' => [$oldNombre, $nombre],
            'codigo' => [$oldCodigo, $codigo],
            'actividad_economica' => [$oldActividad, $actividad],
            'telefono' => [$oldTelefono, $telefono],
        ];

        foreach ($campos as $campo => [$antiguo, $nuevo]) {
            if ($antiguo != $nuevo) {
                $stmtHist = $conexion->prepare("INSERT INTO historial_proveedores (proveedor_id, campo, valor_anterior, valor_nuevo, usuario_id, fecha_modificacion) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmtHist->bind_param("isssi", $id, $campo, $antiguo, $nuevo, $_SESSION['id']);
                $stmtHist->execute();
                $stmtHist->close();
            }
        }

        // Actualizar proveedor
        $stmtUpdate = $conexion->prepare("UPDATE proveedores SET nombre=?, codigo=?, actividad_economica=?, telefono=?, usuario_id=? WHERE id=?");
        $stmtUpdate->bind_param("ssssii", $nombre, $codigo, $actividad, $telefono, $usuario_id, $id);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $mensaje = "Proveedor actualizado correctamente.";
        header("Location: lista_proveedores.php"); exit();
            } else {
                $mensaje = "Error al actualizar proveedor.";
            }
            $stmtUpdate->close();
        }
        $stmtCheck->close();

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Proveedor</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    <div class="contenido">
        <h2>Editar Proveedor</h2>

        <?php if (!empty($mensaje)): ?>
            <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form method="POST" class="formulario">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

            <label for="codigo">NIT/DUI:</label>
            <input type="text" name="codigo" value="<?= htmlspecialchars($codigo) ?>" required>

            <label for="actividad_economica">Actividad Económica:</label>
            <input type="text" name="actividad_economica" value="<?= htmlspecialchars($actividad) ?>" required>

            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($telefono) ?>" required>

            <label for="usuario_id">Usuario Contacto:</label>
            <select name="usuario_id" required>
                <option value="">-- Seleccione un usuario --</option>
                <?php 
                $usuariosRes->data_seek(0);
                while ($u = $usuariosRes->fetch_assoc()): ?>
                    <option value="<?= $u['id'] ?>" <?= $u['id'] == $usuario_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['nombre_completo']) ?> (<?= $u['correo'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" class="btn">Guardar Cambios</button>
            <a href="lista_proveedores.php" class="btn">Volver</a>
        </form>
    </div>
</body>
</html>

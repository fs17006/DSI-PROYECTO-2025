<?php
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
        // Validar que no exista otro proveedor con el mismo código
        $stmtCheck = $conexion->prepare("SELECT id FROM proveedores WHERE codigo=? AND id<>?");
        $stmtCheck->bind_param("si", $codigo, $id);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $mensaje = "Ya existe otro proveedor con este NIT/DUI.";
        } else {
            $stmtUpdate = $conexion->prepare("UPDATE proveedores SET nombre=?, codigo=?, actividad_economica=?, telefono=?, usuario_id=? WHERE id=?");
            $stmtUpdate->bind_param("ssssii", $nombre, $codigo, $actividad, $telefono, $usuario_id, $id);
            $stmtUpdate->execute();

            if ($stmtUpdate->affected_rows >= 0) {
                $mensaje = "Proveedor actualizado correctamente.";
                header("Location: lista_proveedores.php"); exit();
            } else {
                $mensaje = "Error al actualizar proveedor.";
            }
            $stmtUpdate->close();
        }
        $stmtCheck->close();
    }
}

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

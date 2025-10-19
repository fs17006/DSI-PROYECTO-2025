<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR y ESTANDAR pueden agregar
if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'ESTANDAR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";

$mensaje = "";

// Obtener lista de usuarios para asignar al proveedor
$usuariosRes = $conexion->query("SELECT id, nombre_completo, correo FROM usuarios WHERE perfil IN ('ADMINISTRADOR','ESTANDAR')");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $codigo = trim($_POST['codigo'] ?? '');
    $actividad = trim($_POST['actividad_economica'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $usuario_id = (int)($_POST['usuario_id'] ?? 0);

    // Validaciones
    if (empty($nombre) || empty($codigo) || empty($actividad) || empty($telefono) || $usuario_id <= 0) {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        // Validar que el código (NIT/DUI) no exista
        $stmtCheck = $conexion->prepare("SELECT id FROM proveedores WHERE codigo = ?");
        $stmtCheck->bind_param("s", $codigo);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $mensaje = "Ya existe un proveedor con este NIT/DUI.";
        } else {
            // Insertar nuevo proveedor
            $stmt = $conexion->prepare("INSERT INTO proveedores (usuario_id, nombre, codigo, actividad_economica, telefono) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $usuario_id, $nombre, $codigo, $actividad, $telefono);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $mensaje = "Proveedor registrado exitosamente.";
                header("Location: lista_proveedores.php"); exit();
            } else {
                $mensaje = "Error al agregar proveedor.";
            }

            $stmt->close();
        }
        $stmtCheck->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/estilos.css">
    <title>Agregar Proveedor</title>
</head>
<body>
<?php include("includes/navbar.php"); ?>
<div class="contenido">
    <h2>Agregar Proveedor</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form action="agregar_proveedor.php" method="POST" class="formulario">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" required>

        <label for="codigo">NIT/DUI:</label>
        <input type="text" name="codigo" required>

        <label for="actividad_economica">Actividad Económica:</label>
        <input type="text" name="actividad_economica" required>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" required>

        <label for="usuario_id">Usuario Contacto:</label>
        <select name="usuario_id" required>
            <option value="">-- Seleccione un usuario --</option>
            <?php while ($u = $usuariosRes->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre_completo']) ?> (<?= $u['correo'] ?>)</option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn">Guardar Proveedor</button>
        <a href="lista_proveedores.php" class="btn">Volver</a>
    </form>
</div>
</body>
</html>

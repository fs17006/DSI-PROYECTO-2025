<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "conexion.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: clientes.php");
    exit();
}

$stmt = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$cliente = $resultado->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $dui = trim($_POST['dui']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $direccion = trim($_POST['direccion']);

    // Validar DUI duplicado (excepto el del mismo cliente)
    $verificar = $conexion->prepare("SELECT id FROM clientes WHERE dui = ? AND id != ?");
    $verificar->bind_param("si", $dui, $id);
    $verificar->execute();
    $existe = $verificar->get_result();

    if ($existe->num_rows > 0) {
        echo "<script>alert('El DUI ya está registrado para otro cliente.');</script>";
    } else {
        $update = $conexion->prepare("UPDATE clientes SET nombre=?, dui=?, telefono=?, correo=?, direccion=? WHERE id=?");
        $update->bind_param("sssssi", $nombre, $dui, $telefono, $correo, $direccion, $id);
        if ($update->execute()) {
            echo "<script>alert('Cliente actualizado correctamente.'); window.location.href='lista_clientes.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include("includes/navbar.php"); ?>
    <div class="contenido">
        <h2>Editar Cliente</h2>
        <form method="POST" class="formulario">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>

            <label>DUI:</label>
            <input type="text" name="dui" value="<?= htmlspecialchars($cliente['dui']) ?>" required>

            <label>Teléfono:</label>
            <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>">

            <label>Correo:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($cliente['correo']) ?>" required>

            <label>Dirección:</label>
            <input type="text" name="direccion" value="<?= htmlspecialchars($cliente['direccion']) ?>">

            <button type="submit"class="btn">Guardar </button>
        </form>
    </div>
</body>
</html>

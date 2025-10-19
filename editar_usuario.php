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
    $query = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 0) {
        echo "Usuario no encontrado.";
        exit();
    }else {
$usuario = $resultado->fetch_assoc(); //  Aquí se asigna correctamente
    }
}elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar la edición del usuario
    $id = intval($_POST['id']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $usuario = $_POST['usuario'];
    $correo = trim($_POST['correo']);
    $perfil = $_POST['perfil'];
    $activo = $_POST['activo'];

    $update = $conexion->prepare("UPDATE usuarios SET nombre_completo = ?, usuario = ?, correo = ?, perfil = ?, activo = ? WHERE id = ?");
    $update->bind_param("ssssii", $nombre_completo, $usuario, $correo, $perfil, $activo, $id);

     if ($update->execute()) {
        echo "<script>alert('Usuario actualizado correctamente'); window.location.href='lista_usuarios.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error al actualizar el usuario'); window.location.href='editar_usuario.php?id=$id';</script>";
    }

    $update->close();
    $conexion->close();
    exit();
}
// --- Si no hay ni GET ni POST válido ---
else {
    echo "Solicitud no válida.";
    exit();
}
$conexion->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/estilos.css">
    <title>Editar Usuario</title>
</head>
<body>
    <div class="contenido">
        <h2>Editar Usuario</h2>

        <form action="editar_usuario.php" method="POST" class="formulario">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id'] ?? '') ?>">

            <label for="nombre_completo">Nombre completo:</label>
            <input type="text" name="nombre_completo" value="<?= htmlspecialchars($usuario['nombre_completo'] ?? '') ?>" required>


            <label for="usuario">Nombre de usuario:</label>
            <input type="text" name="usuario" value="<?= htmlspecialchars($usuario['usuario'] ?? '') ?>" required>

            <label for="correo">Correo electrónico:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" required>


            <label for="perfil">Perfil:</label>
            <select name="perfil" required>
                <option value="ADMINISTRADOR" <?= (isset($usuario['perfil']) && $usuario['perfil'] === 'ADMINISTRADOR') ? 'selected' : '' ?>>ADMINISTRADOR</option>
                <option value="MONITOREO" <?= (isset($usuario['perfil']) && $usuario['perfil'] === 'MONITOREO') ? 'selected' : '' ?>>MONITOREO</option>
                <option value="ESTANDAR" <?= (isset($usuario['perfil']) && $usuario['perfil'] === 'ESTANDAR') ? 'selected' : '' ?>>ESTANDAR</option>
            </select>

            <label for="activo">Estado:</label>
            <select name="activo" required>
                <option value="1" <?= (isset($usuario['activo']) && $usuario['activo'] === '1') ? 'selected' : '' ?>>activo</option>
                <option value="0" <?= (isset($usuario['activo']) && $usuario['activo'] === '0') ? 'selected' : '' ?>>inactivo</option>
            </select>

            <button type="submit" class="btn">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
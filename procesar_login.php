<?php
session_start();
require "conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    // Consulta segura usando prepared statements
    $sql = "SELECT usuario, contrasena, perfil, activo FROM usuarios WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $row = $resultado->fetch_assoc();

        // Verificar si el usuario está activo
        if (!$row['activo']) {
            echo "<script>alert('Este usuario ha sido deshabilitado. Contacte al administrador.'); window.location.href='index.php';</script>";
            exit();
        }

        // Verificar contraseña encriptada
        if (password_verify($contrasena, $row['contrasena'])) {
            // Iniciar sesión
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['perfil'] = $row['perfil'];

            header("Location: plantilla.php");
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta.'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado.'); window.location.href='index.php';</script>";
    }

    $stmt->close();
    $conexion->close();
}
?>

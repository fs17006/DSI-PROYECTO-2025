<?php
session_start();
require "conexion.php";

function obtenerIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    // Consulta segura
    $sql = "SELECT id, usuario, contrasena, perfil, activo FROM usuarios WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $row = $resultado->fetch_assoc();

        // Usuario activo?
        if (!$row['activo']) {
            echo "<script>alert('Este usuario ha sido deshabilitado. Contacte al administrador.'); window.location.href='index.php';</script>";
            exit();
        }

        // Validar contraseña
        if (password_verify($contrasena, $row['contrasena'])) {

            // Iniciar sesión
            $_SESSION['id'] = $row['id'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['perfil'] = $row['perfil'];

            // Registrar acceso
            $ip = obtenerIP();
            $logSql = "INSERT INTO historial_accesos (usuario_id, usuario, ip) VALUES (?, ?, ?)";
            $logStmt = $conexion->prepare($logSql);
            $logStmt->bind_param("iss", $row['id'], $row['usuario'], $ip);
            $logStmt->execute();
            $logStmt->close();

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

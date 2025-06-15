<?php
session_start();

$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

$usuarios = [
    'administrador' => ['clave' => '123', 'perfil' => 'ADMINISTRADOR'],
    'monitoreo' => ['clave' => '123', 'perfil' => 'MONITOREO'],
    'estandar' => ['clave' => '123', 'perfil' => 'ESTANDAR'],
];

if (isset($usuarios[$usuario]) && $usuarios[$usuario]['clave'] === $contrasena) {
    $_SESSION['usuario'] = $usuario;
    $_SESSION['perfil'] = $usuarios[$usuario]['perfil'];
    header('Location: plantilla.php');
} else {
    echo "Credenciales incorrectas.";
}
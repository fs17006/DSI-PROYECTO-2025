<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

$conexion = new mysqli("sql305.infinityfree.com","if0_39903381", "fYzFQCTA5mOH8","if0_39903381_sistema_web");
    $conexion->query("DELETE FROM facturas WHERE id = $id");

    header("Location: lista_facturas.php");
    exit();
}
?>

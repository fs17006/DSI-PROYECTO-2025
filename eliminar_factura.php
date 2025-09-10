<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

require "conexion.php";   
$conexion->query("DELETE FROM facturas WHERE id = $id");

    header("Location: lista_facturas.php");
    exit();
}
?>

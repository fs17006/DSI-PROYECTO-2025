<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

if ($_SESSION['perfil'] !== 'ADMINISTRADOR') {
    echo "Acceso denegado.";
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: lista_proveedores.php");
    exit();
}

$conexion = new mysqli("sql305.infinityfree.com","if0_39903381", "fYzFQCTA5mOH8","if0_39903381_sistema_web");if ($conexion->connect_errno) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Opcional: verificar que no existan facturas vinculadas antes de eliminar
$stmt_check = $conexion->prepare("SELECT COUNT(*) as total FROM facturas WHERE proveedor_id = ?");
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row = $result_check->fetch_assoc();
if ($row['total'] > 0) {
    echo "No se puede eliminar el proveedor porque tiene facturas relacionadas.";
    exit();
}

$stmt_check->close();

// Eliminar proveedor
$stmt = $conexion->prepare("DELETE FROM proveedores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: lista_proveedores.php");
    exit();
} else {
    echo "Error al eliminar proveedor.";
}

$stmt->close();
$conexion->close();
?>

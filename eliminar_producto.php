<?php
require "conexion.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: lista_productos.php");
    exit();
}

// Verificar si el producto está asociado a facturas activas
$query = "
    SELECT COUNT(*) 
    FROM detalle_factura df
    INNER JOIN facturas f ON df.factura_id = f.id
    WHERE df.producto_id = ?
";
$check = $conexion->prepare($query);
$check->bind_param("i", $id);
$check->execute();
$check->bind_result($count);
$check->fetch();
$check->close();

// Si está en alguna factura, no se puede eliminar
if ($count > 0) {
    echo "<script>
        alert('No se puede eliminar este producto porque está asociado a facturas.');
        window.location='lista_productos.php';
    </script>";
    exit();
}

//  Si no está asociado, se puede eliminar
$stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script>
        alert('Producto eliminado correctamente.');
        window.location='lista_productos.php';
    </script>";
} else {
    echo "<script>
        alert('Error al eliminar el producto.');
        window.location='lista_productos.php';
    </script>";
}

$stmt->close();
$conexion->close();
exit();
?>

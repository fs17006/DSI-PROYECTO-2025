<?php
$perfil = $_SESSION['perfil'];
?>
<div class="navbar">
    <a href="plantilla.php" class="btn">
    <h2>Sistema de gestion de proveedores</h2>
</a>

    <ul class="navbar-menu">
        <?php if ($perfil == 'ADMINISTRADOR'): ?>
            <li><a href="lista_pedidos.php">Administrar Pedidos</a></li>
        <?php endif; ?>
        <li><a href="lista_proveedores.php">Administrar Proveedor</a></li>
        <li><a href="lista_usuarios.php">Administrar Usuarios</a></li>
        <li><a href="lista_facturas.php">Administrar Facturas</a></li>
        <li><a href="lista_clientes.php">Administrar Clientes</a></li>
        <li><a href="estado_pagos.php">Estado de Pagos</a></li>
        <li><a href="accesos.php">Historial de acceso</a></li>

        <?php if ($perfil === 'ADMINISTRADOR' || $perfil === 'CONTADOR'): ?>
            <li><a href="registrar_pago.php">Registrar Pagos</a></li>
        <?php endif; ?>

        <li><a href="lista_productos.php">Administrar Productos</a></li>
        <li><a href="inventario.php">Dashboard</a></li>
        <li class="logout"><a href="logout.php">Cerrar sesión</a></li>
    </ul>
</div>

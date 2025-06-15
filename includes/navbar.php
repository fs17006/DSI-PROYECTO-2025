<?php
$perfil = $_SESSION['perfil'];
?>
<div class="navbar">
    <h1 class="navbar-title">Sistema de Gestión de Proveedores</h1>
    <ul class="navbar-menu">
        <?php if ($perfil == 'ADMINISTRADOR'): ?>
            <li><a href="plantilla.php">Panel Principal</a></li>
        <?php endif; ?>
        <li><a href="lista_proveedores.php">Administrar Proveedor</a></li>
        <li><a href="lista_facturas.php">Administrar Facturas</a></li>
        <li><a href="estado_pagos.php">Estado de Pagos</a></li>

        <?php if ($perfil === 'ADMINISTRADOR' || $perfil === 'CONTADOR'): ?>
            <li><a href="registrar_pago.php">Registrar Pagos</a></li>
        <?php endif; ?>

        <li><a href="#">Generar Reportes</a></li>
        <li><a href="#">Consultar Dashboard</a></li>
        <li class="logout"><a href="logout.php">Cerrar sesión</a></li>
    </ul>
</div>

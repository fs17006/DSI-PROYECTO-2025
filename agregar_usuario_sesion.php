<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Solo ADMINISTRADOR y ESTANDAR pueden agregar usuarios
if ($_SESSION['perfil'] !== 'ADMINISTRADOR' && $_SESSION['perfil'] !== 'ESTANDAR') {
    echo "Acceso denegado.";
    exit();
}

require "conexion.php";

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if (empty($nombre_completo) || empty($correo) || empty($usuario) || empty($contrasena)) {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        // Encriptar contraseña
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar nuevo usuario
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo, usuario, contrasena) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre_completo, $correo, $usuario, $hash);

        if ($stmt->execute()) {
            $mensaje = "Usuario registrado exitosamente.";
            header("Location: lista_usuarios.php"); exit();
        } else {
            $mensaje = "Error al agregar usuario. Puede que el usuario o correo ya existan.";
        }

        $stmt->close();
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/estilos.css">
    <title>Agregar Usuario</title>
    <style>
        .required::after {
            content: " *";
            color: red;
        }
        .error-message {
            color: red;
            display: none;
            margin-bottom: 10px;
        }
        input:invalid {
            border-color: red;
        }
    </style>
</head>
<body>
<?php include("includes/navbar.php"); ?>
<div class="contenido">
    <h2>Agregar Usuario</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="error-message" style="display:block;"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <div class="error-message" id="errorMsg">Por favor complete todos los campos obligatorios.</div>

    <form id="usuarioForm" action="agregar_usuario.php" method="POST" class="formulario">
        <label for="nombre_completo" class="required">Nombre completo:</label>
        <input type="text" name="nombre_completo" required>

        <label for="correo" class="required">Correo electrónico:</label>
        <input type="email" name="correo" required>

        <label for="usuario" class="required">Usuario:</label>
        <input type="text" name="usuario" required>

        <label for="contrasena" class="required">Contraseña:</label>
        <input type="password" name="contrasena" required>

       
            <button type="submit" class="btn">Guardar</button>
            <a href="lista_usuarios.php" class="btn">Volver</a>
        
    </form>

    

</div>

<script>
    const form = document.getElementById('usuarioForm');
    const errorMsg = document.getElementById('errorMsg');

    form.addEventListener('submit', function(e) {
        let allFilled = true;
        form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value.trim()) {
                allFilled = false;
            }
        });

        if (!allFilled) {
            e.preventDefault(); // Evita enviar el formulario
            errorMsg.style.display = 'block'; // Muestra mensaje de error
        } else {
            errorMsg.style.display = 'none';
        }
    });
</script>
</body>
</html>

<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <link rel="stylesheet" href="css/estilos.css" />
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
    <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <form id="loginForm" action="procesar_login.php" method="POST" autocomplete="off">
            <div class="error-message" id="errorMsg">Por favor complete todos los campos obligatorios.</div>

            <label for="usuario" class="required">Usuario:</label>
            <input type="text" name="usuario" id="usuario" required autofocus />

            <label for="contrasena" class="required">Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena" required />

            <input type="submit" id="btnSubmit" value="Entrar" />
        </form>

        <form action="agregar_usuario.php" method="GET" style="margin-top: 15px;">
            <input type="submit" value="Crear Usuario" />
        </form>
    </div>

    <script>
        const form = document.getElementById('loginForm');
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

<?php
session_start();
include 'config.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($usuario)) {
        $mensaje = 'El nombre de usuario es obligatorio.';
        $tipo_mensaje = 'danger';
    } elseif (strlen($usuario) < 3) {
        $mensaje = 'El nombre de usuario debe tener al menos 3 caracteres.';
        $tipo_mensaje = 'danger';
    } elseif (empty($contrasena) || strlen($contrasena) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres.';
        $tipo_mensaje = 'danger';
    } else {
        $sql_check = "SELECT id FROM gestion_usuarios_login WHERE nombre = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result_check = $stmt->get_result();

        if ($result_check->num_rows > 0) {
            $mensaje = 'El nombre de usuario "' . htmlspecialchars($usuario) . '" ya está registrado. Elige otro.';
            $tipo_mensaje = 'danger';
        } else {
            $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO gestion_usuarios_login (nombre, email, password, rol, activo) VALUES (?, ?, ?, 'usuario', 1)";
            $stmt = $conn->prepare($sql_insert);
            $email_default = null;
            $stmt->bind_param("sss", $usuario, $email_default, $hash_contrasena);

            if ($stmt->execute()) {
                $mensaje = '✅ ¡Usuario registrado exitosamente! Tu usuario es: <strong>' . htmlspecialchars($usuario) . '</strong>';
                $tipo_mensaje = 'success';
                $usuario = '';
                $contrasena = '';
            } else {
                $mensaje = '❌ Error al registrar: ' . $conn->error;
                $tipo_mensaje = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Gestión</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0d1b2a, #1a3a5c, #2c5f7c);
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:20px;
        }
        .container {
            background:#fff;
            border-radius:16px;
            padding:40px 35px;
            max-width:420px;
            width:100%;
            box-shadow:0 20px 60px rgba(0,0,0,0.4);
        }
        .logo { text-align:center; margin-bottom:25px; }
        .logo h1 { color:#1a3a5c; font-size:28px; font-weight:700; }
        .logo p { color:#6c757d; font-size:14px; margin-top:5px; }
        .alert {
            padding:12px 16px;
            border-radius:8px;
            margin-bottom:20px;
            font-weight:500;
            font-size:14px;
        }
        .alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .alert-danger { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .form-group { margin-bottom:18px; }
        .form-group label {
            display:block;
            font-weight:600;
            color:#333;
            margin-bottom:6px;
            font-size:14px;
        }
        .form-group input {
            width:100%;
            padding:12px 15px;
            border:2px solid #e0e4ea;
            border-radius:8px;
            font-size:14px;
            background:#f8f9fa;
            transition:all 0.3s;
        }
        .form-group input:focus {
            border-color:#1a3a5c;
            outline:none;
            background:#fff;
            box-shadow:0 0 0 4px rgba(26,58,92,0.1);
        }
        .form-group input::placeholder { color:#aaa; }
        .btn {
            width:100%;
            padding:14px;
            background:linear-gradient(135deg, #1a3a5c, #2c5f7c);
            color:#fff;
            border:none;
            border-radius:8px;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
            transition:all 0.3s;
        }
        .btn:hover {
            transform:translateY(-2px);
            box-shadow:0 8px 25px rgba(26,58,92,0.3);
        }
        .footer-links {
            text-align:center;
            margin-top:20px;
            font-size:14px;
            color:#6c757d;
        }
        .footer-links a { color:#1a3a5c; text-decoration:none; font-weight:600; }
        .footer-links a:hover { text-decoration:underline; }
        .footer {
            text-align:center;
            margin-top:20px;
            font-size:12px;
            color:#999;
            border-top:1px solid #eee;
            padding-top:20px;
        }
        .hint {
            display:block;
            margin-top:4px;
            font-size:12px;
            color:#6c757d;
        }
        .requerido { color:#dc3545; }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <h1>📝 Crear Cuenta</h1>
        <p>Regístrate para acceder al sistema</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>👤 Usuario <span class="requerido">*</span></label>
            <input type="text" name="usuario" placeholder="Ej: AUGM, JPEREZ" required maxlength="20" pattern="[A-Za-z0-9]+">
            <span class="hint">💡 Ejemplos: <strong>AUGM</strong>, <strong>augm</strong>, <strong>JPEREZ</strong></span>
        </div>

        <div class="form-group">
            <label>🔒 Contraseña <span class="requerido">*</span></label>
            <input type="password" name="contrasena" placeholder="Mínimo 6 caracteres" required minlength="6">
        </div>

        <button type="submit" class="btn">✅ Registrarse</button>
    </form>

    <div class="footer-links">
        ¿Ya tienes cuenta? <a href="login.php">Iniciar Sesión</a>
    </div>
    <div class="footer">Sistema de Gestión - Secretaría de Finanzas de Chiapas</div>
</div>

<script>
    document.querySelector('input[name="usuario"]').addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-z0-9]/g, '');
    });
</script>
</body>
</html>
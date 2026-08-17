<?php
session_start();
include 'config.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($nombre) || empty($contrasena)) {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo_mensaje = 'danger';
    } else {
        // LEEMOS EL ROL AL INICIAR SESIÓN
        $sql = "SELECT id, nombre, email, password, rol FROM gestion_usuarios_login WHERE nombre = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $usuario = $result->fetch_assoc();
            if (password_verify($contrasena, $usuario['password'])) {
                // GUARDAMOS EL ROL EN LA SESIÓN
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol']; 
                header('Location: menu.php');
                exit;
            } else {
                $mensaje = '❌ Contraseña incorrecta.';
                $tipo_mensaje = 'danger';
            }
        } else {
            $mensaje = '❌ El usuario no existe.';
            $tipo_mensaje = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
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
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <h1>🔐 Iniciar Sesión</h1>
        <p>Ingresa tus credenciales para acceder al sistema</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>👤 Usuario</label>
            <input type="text" name="nombre" placeholder="Ingresa tu usuario" required>
        </div>

        <div class="form-group">
            <label>🔒 Contraseña</label>
            <input type="password" name="contrasena" placeholder="Ingresa tu contraseña" required>
        </div>

        <button type="submit" class="btn">🚀 Iniciar Sesión</button>
    </form>

    <div class="footer-links">
        ¿No tienes cuenta? <a href="registro_usuario.php">Regístrate aquí</a>
    <div class="footer">Sistema de Gestión - Secretaría de Finanzas de Chiapas</div>
</div>
</body>
</html>
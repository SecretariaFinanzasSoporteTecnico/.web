<?php
session_start();

// =============================================
// VERIFICAR SI EL USUARIO ESTÁ LOGUEADO
// =============================================
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include 'config.php';

// =============================================
// INFORMACIÓN DEL USUARIO LOGUEADO
// =============================================
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_email = $_SESSION['usuario_email'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? 'usuario'; // Capturamos el rol

// =========================================================
// LOGICA PARA VINCULAR EL LOGO
// =========================================================
$ruta_logo_web = '';
foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
    $archivo = __DIR__ . '/logos/logo_izquierdo.' . $ext;
    if (file_exists($archivo)) {
        $ruta_logo_web = 'logos/logo_izquierdo.' . $ext . '?v=' . filemtime($archivo);
        break;
    }
}
$mostrar_logo = !empty($ruta_logo_web);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - Sistema de Gestión</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e8edf5 0%, #d5dce6 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        /* ========================================
           HEADER DEL USUARIO (MEJORADO)
           ======================================== */
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 12px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid #e8edf5;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .user-header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-header .user-info .avatar {
            width: 45px;
            height: 45px;
            background: #1a3a5c;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            border: 2px solid #e8edf5;
        }
        .user-header .user-info .user-name {
            font-weight: 600;
            color: #1a3a5c;
            font-size: 16px;
        }
        .user-header .user-info .user-email {
            font-size: 13px;
            color: #6c757d;
        }

        /* 👇 ESTILOS PARA LOS ROLES 👇 */
        .badge-rol {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
            text-transform: capitalize;
        }
        .rol-admin {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
        .rol-usuario {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .btn-logout {
            background: #dc3545;
            color: #fff;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .header-logo { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #1a3a5c; }
        .logo-img-institucional { max-height: 100px; width: auto; display: block; margin: 0 auto; }
        .logo-textos-respaldo { display: flex; flex-direction: column; align-items: center; line-height: 1.3; }
        .logo-textos-respaldo .humanismo { font-size: 18px; font-weight: 700; color: #1a3a5c; letter-spacing: 3px; }
        .logo-textos-respaldo .secret { font-size: 28px; font-weight: 800; color: #b8860b; letter-spacing: 2px; margin: 5px 0; }
        .logo-textos-respaldo .gobierno { font-size: 18px; font-weight: 600; color: #1a3a5c; }
        .logo-textos-respaldo .periodo { font-size: 16px; font-weight: 600; color: #1a3a5c; }

        .titulo-menu { font-size: 32px; font-weight: 700; color: #1a3a5c; margin-bottom: 10px; }
        .subtitulo-menu { font-size: 16px; color: #6c757d; margin-bottom: 40px; }

        .menu-botones { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 20px; }
        .btn-menu {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 20px; border-radius: 16px; text-decoration: none; transition: all 0.4s ease;
            border: 2px solid #e6eaef; background: #f8f9fa; min-height: 200px;
        }
        .btn-menu:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15); }
        .btn-menu .icono { font-size: 64px; margin-bottom: 15px; }
        .btn-menu .titulo-btn { font-size: 22px; font-weight: 700; color: #1a3a5c; }
        .btn-menu .descripcion-btn { font-size: 14px; color: #6c757d; margin-top: 5px; }

        .btn-usuario { background: linear-gradient(135deg, #e8f0fe 0%, #d4e2f7 100%); border-color: #1a3a5c; }
        .btn-usuario:hover { background: linear-gradient(135deg, #1a3a5c 0%, #0f2a44 100%); border-color: #1a3a5c; }
        .btn-usuario:hover .titulo-btn { color: #fff; }
        .btn-usuario:hover .descripcion-btn { color: #c8d6e5; }

        .btn-servicio { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-color: #2e7d32; }
        .btn-servicio:hover { background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%); border-color: #2e7d32; }
        .btn-servicio:hover .titulo-btn { color: #fff; }
        .btn-servicio:hover .descripcion-btn { color: #c8e6c9; }

        .footer-menu { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e6eaef; color: #999; font-size: 13px; }

        @media (max-width: 768px) {
            .container { padding: 25px 20px; }
            .user-header { flex-direction: column; text-align: center; }
            .user-header .user-info { flex-direction: column; text-align: center; }
            .menu-botones { grid-template-columns: 1fr; gap: 15px; }
            .btn-menu { min-height: 150px; padding: 30px 20px; }
            .btn-menu .icono { font-size: 48px; }
            .titulo-menu { font-size: 26px; }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- HEADER DEL USUARIO MEJORADO -->
        <div class="user-header">
            <div class="user-info">
                <div class="avatar">
                    <?php echo strtoupper(substr($usuario_nombre, 0, 1)); ?>
                </div>
                <div>
                    <div class="user-name">
                        👤 <?php echo htmlspecialchars($usuario_nombre); ?>
                        <!-- ETIQUETA DE ROL CON COLORES -->
                        <span class="badge-rol <?php echo ($usuario_rol == 'admin') ? 'rol-admin' : 'rol-usuario'; ?>">
                            <?php echo ucfirst($usuario_rol); ?>
                        </span>
                    </div>
                    <div class="user-email">📧 <?php echo htmlspecialchars($usuario_email); ?></div>
                </div>
            </div>
            <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
        </div>

        <!-- LOGO Y TEXTOS INSTITUCIONALES -->
        <div class="header-logo">
            <img src="<?php echo $ruta_logo_web; ?>" alt="Logotipo" class="logo-img-institucional" onerror="this.style.display='none'">
            <div class="logo-textos-respaldo" style="<?php echo $mostrar_logo ? 'display:none;' : ''; ?>">
                <span class="humanismo">HUMANISMO QUE TRANSFORMA</span>
                <span class="secret">SECRETARÍA DE FINANZAS</span>
                <span class="gobierno">GOBIERNO DE CHIAPAS</span>
                <span class="periodo">2024 - 2030</span>
            </div>
        </div>

        <h1 class="titulo-menu">📋 Menú Principal</h1>
        <p class="subtitulo-menu">Selecciona una opción para continuar</p>

        <div class="menu-botones">
            
            <!-- BOTÓN DE GESTIÓN DE USUARIO -->
            <a href="index.php" class="btn-menu btn-usuario">
                <span class="icono">👤</span>
                <span class="titulo-btn">📊 Gestión de Usuario</span>
                <span class="descripcion-btn">Administrar usuarios, faltas e incidencias</span>
            </a>

            <!-- BOTÓN DE GESTIÓN DE SERVICIO -->
            <a href="gestion_servicio.php" class="btn-menu btn-servicio">
                <span class="icono">🛠️</span>
                <span class="titulo-btn">📋 Gestión de Servicio</span>
                <span class="descripcion-btn">Administrar servicios y solicitudes</span>
            </a>

        </div>

        <div class="footer-menu">
            <p>Sistema de Gestión - Secretaría de Finanzas de Chiapas</p>
            <p style="margin-top: 5px; font-size: 12px;"><?php echo date('d/m/Y, h:i:s a'); ?></p>
        </div>

    </div>

    <script>
        function actualizarReloj() {
            const ahora = new Date();
            const opciones = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            document.querySelector('.footer-menu p:last-child').textContent = ahora.toLocaleDateString('es-MX', opciones);
        }
        actualizarReloj();
        setInterval(actualizarReloj, 1000);
    </script>
</body>
</html>
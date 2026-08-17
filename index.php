<?php
session_start();
include 'config.php';

$logueado = isset($_SESSION['usuario_id']);
$usuario_rol = $_SESSION['usuario_rol'] ?? 'usuario'; // Capturamos el rol del que ve la página

// ===== RUTAS DE LOGOS =====
function ruta_logo($slot, $default) {
    foreach (['png', 'jpg', 'webp'] as $ext) {
        $ruta = __DIR__ . '/logos/logo_' . $slot . '.' . $ext;
        if (file_exists($ruta)) {
            return 'logos/logo_' . $slot . '.' . $ext . '?v=' . filemtime($ruta);
        }
    }
    return $default;
}
$logo_izquierdo = ruta_logo('izquierdo', 'OIP.png.jpg');
$logo_derecho   = ruta_logo('derecho', '');

// ===== OBTENER EL PERIODO =====
$periodo = '2024-2030';
$sql_textos = "SELECT valor FROM configuracion WHERE clave = 'periodo'";
$result_textos = $conn->query($sql_textos);
if ($result_textos && $result_textos->num_rows > 0) {
    $row = $result_textos->fetch_assoc();
    $periodo = $row['valor'];
}

// ===== CAPTURAR MENSAJES =====
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipo_mensaje = 'success';
}
if (isset($_GET['error'])) {
    $mensaje = $_GET['error'];
    $tipo_mensaje = 'danger';
}

// ===== BUSCADOR =====
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
if ($busqueda) {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre LIKE ? OR email LIKE ? ORDER BY id DESC");
    $like = '%' . $busqueda . '%';
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuario</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; min-height:100vh; padding:20px; }
        .container { max-width:1200px; margin:0 auto; background:#fff; border-radius:12px; padding:25px 30px; box-shadow:0 8px 30px rgba(0,0,0,0.1); }
        .header-superior { display: flex; align-items: center; justify-content: space-between; padding-bottom: 10px; border-bottom: 2px solid #1a3a5c; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .header-superior .logo-izquierda { display: flex; align-items: center; gap: 15px; }
        .header-superior .logo-izquierda .textos-logo { display: flex; flex-direction: column; line-height: 1.2; }
        .header-superior .logo-izquierda .textos-logo .humanismo { font-size: 14px; font-weight: 700; color: #1a3a5c; letter-spacing: 1px; }
        .header-superior .logo-izquierda .textos-logo .secret { font-size: 16px; font-weight: 800; color: #b8860b; letter-spacing: 0.5px; }
        .header-superior .logo-izquierda .textos-logo .gobierno { font-size: 14px; font-weight: 600; color: #1a3a5c; }
        .header-superior .logo-izquierda .textos-logo .periodo { font-size: 13px; font-weight: 600; color: #1a3a5c; }

        .logo-editable { position: relative; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; border-radius: 8px; overflow: hidden; }
        .logo-editable img { display: block; }
        .logo-editable .logo-overlay { position: absolute; inset: 0; background: rgba(26, 58, 92, 0.55); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; text-align: center; opacity: 0; transition: opacity 0.2s; padding: 4px; }
        .logo-editable:hover .logo-overlay { opacity: 1; }
        .logo-editable input[type="file"] { display: none; }

        .reloj-moderno {
            font-size: 16px; font-weight: 700; color: #1a3a5c;
            background: #f8f9fa; padding: 8px 16px; border-radius: 8px;
            border: 1px solid #e0e0e0; font-family: 'Courier New', monospace;
            text-align: center; display: flex; flex-direction: column; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .reloj-moderno .fecha { font-size: 12px; color: #6c757d; font-weight: 600; }
        .reloj-moderno .hora { font-size: 20px; }

        .header-titulo { text-align: center; margin-bottom: 25px; }
        .titulo-gestion { font-size: 28px; font-weight: 700; color: #1a3a5c; display: inline-block; padding: 0 5px; border-bottom: 3px solid #1a3a5c; }

        .header-actions { display: flex; justify-content: center; margin-bottom: 25px; }
        .header-buttons { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
        .btn { padding: 10px 22px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .btn-agregar { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #fff; }
        .btn-blanco { background: #6c757d; color: #fff; }
        .btn-imprimir { background: #1a3a5c; color: #fff; }
        .btn-faltas { background: #17a2b8; color: #fff; }
        .btn-incidencias { background: #6f42c1; color: #fff; }

        .buscador-moderno { display: flex; justify-content: center; gap: 10px; margin-bottom: 25px; }
        .buscador-moderno input { width: 100%; max-width: 500px; padding: 10px 15px; border: 1px solid #dce1e8; border-radius: 8px; font-size: 15px; transition: 0.3s; background: #f8f9fa; }
        .buscador-moderno input:focus { border-color: #1a3a5c; outline: none; background: #fff; box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1); }

        .table-responsive { overflow-x: auto; margin-top: 15px; border-radius: 8px; border: 1px solid #e6eaef; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        thead { background: #1a3a5c; color: #fff; }
        th { padding: 12px 15px; text-align: left; font-weight: 600; }
        td { padding: 12px 15px; border-bottom: 1px solid #e6eaef; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f5f8fc; }

        .acciones { display: flex; gap: 6px; flex-wrap: wrap; }
        .btn-small { font-size: 12px; padding: 5px 12px; border-radius: 6px; }
        .btn-warning-small { background: #ffc107; color: #1e2a3a; }
        .btn-danger-small { background: #dc3545; color: #fff; }
        .btn-secondary-small { background: #6c757d; color: #fff; }

        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: inherit; opacity: 0.7; }
        .close-btn:hover { opacity: 1; }

        @media (max-width: 768px) {
            .header-superior { flex-direction: column; align-items: center; text-align: center; }
            .header-superior .logo-izquierda { flex-direction: column; text-align: center; }
            .reloj-moderno { width: 100%; }
            .header-buttons { flex-direction: column; width: 100%; }
            .header-buttons .btn { width: 100%; text-align: center; }
            .buscador-moderno { flex-direction: column; align-items: center; }
            .buscador-moderno input { max-width: 100%; width: 100%; }
            .acciones { flex-direction: column; }
            .acciones .btn { width: 100%; text-align: center; }
        }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header-superior">
            <div class="logo-izquierda">
                <!-- ✅ SOLO LOS ADMINISTRADORES PUEDEN CAMBIAR EL LOGO -->
                <?php if ($logueado && $usuario_rol == 'admin'): ?>
                    <label class="logo-editable" title="Haz clic para cambiar el logo">
                        <img src="<?php echo htmlspecialchars($logo_izquierdo); ?>" alt="Logotipo" style="height:65px; width:auto;">
                        <span class="logo-overlay">📷 Cambiar</span>
                        <input type="file" accept="image/png,image/jpeg,image/webp" data-slot="izquierdo" class="input-logo">
                    </label>
                <?php else: ?>
                    <!-- SI ES USUARIO, SOLO VE LA IMAGEN SIN EL BOTÓN -->
                    <img src="<?php echo htmlspecialchars($logo_izquierdo); ?>" alt="Logotipo" style="height:65px; width:auto;">
                <?php endif; ?>

                <div class="textos-logo">
                    <span class="humanismo">HUMANISMO QUE TRANSFORMA</span>
                    <span class="secret">SECRETARÍA DE FINANZAS</span>
                    <span class="gobierno">GOBIERNO DE CHIAPAS</span>
                    <span class="periodo"><?php echo $periodo; ?></span>
                </div>
            </div>
            
            <div class="reloj-moderno" id="reloj">
                <span class="fecha" id="fecha">Cargando...</span>
                <span class="hora" id="hora">Cargando...</span>
            </div>
        </div>

        <div class="header-titulo">
            <span class="titulo-gestion">📊 Gestión de Usuario</span>
        </div>

        <div class="header-actions">
            <div class="header-buttons no-print">
                <!-- ✅ SOLO ADMINISTRADORES PUEDEN AGREGAR USUARIOS -->
                <?php if ($usuario_rol == 'admin'): ?>
                    <a href="agregar.php" class="btn btn-agregar">➕ Agregar Usuario</a>
                <?php endif; ?>
                
                <a href="imprimir_formato_blanco_v2.php" target="_blank" class="btn btn-blanco">📄 Formato en Blanco</a>
                <button onclick="window.print()" class="btn btn-imprimir">🖨️ Imprimir todo</button>
            </div>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo htmlspecialchars($tipo_mensaje); ?> no-print">
                <?php echo htmlspecialchars($mensaje); ?>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">✕</button>
            </div>
        <?php endif; ?>

        <div class="buscador-moderno no-print">
            <form method="GET" style="display: flex; gap: 10px; width: 100%; justify-content: center; flex-wrap: wrap;">
                <input type="text" name="buscar" placeholder="🔍 Buscar por nombre o email..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn btn-primary" style="background: #1a3a5c; color: #fff; padding: 10px 25px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Buscar</button>
                <?php if ($busqueda): ?>
                    <a href="index.php" class="btn btn-danger" style="background: #dc3545; color: #fff; padding: 10px 25px; border: none; border-radius: 8px; text-decoration: none; font-weight: 600;">Limpiar</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Área</th>
                        <th>Puesto</th>
                        <th>Fecha Registro</th>
                        <th class="no-print" style="width: 250px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): 
                        $contador = 0;
                        while($row = $resultado->fetch_assoc()): 
                            $contador++;
                    ?>
                            <tr>
                                <td style="font-weight: 600; color: #1a3a5c;"><?php echo $contador; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['nombre']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                <td><?php echo htmlspecialchars($row['area']); ?></td>
                                <td><?php echo htmlspecialchars($row['puesto']); ?></td>
                                <td style="font-size: 13px; color: #555;"><?php echo date('d/m/Y H:i', strtotime($row['fecha_registro'])); ?></td>
                                <td class="no-print">
                                    <div class="acciones">
                                        <?php if ($usuario_rol == 'admin'): ?>
                                            <!-- SOLO ADMINISTRADORES VEN ESTO -->
                                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn btn-warning-small btn-small">✏️ Editar</a>
                                            <a href="eliminar.php?id=<?php echo $row['id']; ?>" class="btn btn-danger-small btn-small" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">🗑️ Eliminar</a>
                                        <?php else: ?>
                                            <!-- USUARIOS NORMALES VEN UN CANDADO -->
                                            <span style="color:#999; font-size:11px; display:inline-block; background:#f1f1f1; padding:4px 8px; border-radius:4px;">🔒 Sin permisos</span>
                                        <?php endif; ?>
                                        <!-- TODOS PUEDEN VER FALTAS, INCIDENCIAS E IMPRIMIR -->
                                        <a href="faltas.php?usuario_id=<?php echo $row['id']; ?>" class="btn btn-faltas btn-small">📋 Faltas</a>
                                        <a href="ver_incidencia.php?usuario_id=<?php echo $row['id']; ?>" class="btn btn-incidencias btn-small">📄 Incidencias</a>
                                        <a href="imprimir_usuario.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-secondary-small btn-small">🖨️ Imprimir</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">📭 No hay usuarios registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: left; margin-top: 20px;" class="no-print">
            <a href="menu.php" class="btn btn-secondary" style="background: #6c757d; color: #fff; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">⬅ Volver al Menú</a>
        </div>

    </div>

    <script>
        // ===== RELOJ EN TIEMPO REAL =====
        function actualizarReloj() {
            const ahora = new Date();
            const fechaFormateada = ahora.toLocaleDateString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit' });
            const horas = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');
            const segundos = String(ahora.getSeconds()).padStart(2, '0');
            document.getElementById('fecha').textContent = '📅 ' + fechaFormateada;
            document.getElementById('hora').textContent = '🕐 ' + horas + ':' + minutos + ':' + segundos;
        }
        actualizarReloj();
        setInterval(actualizarReloj, 1000);

        // ===== SUBIR LOGO (SOLO APARECE EN EL HTML SI ES ADMIN) =====
        document.querySelectorAll('.input-logo').forEach(function(input) {
            input.addEventListener('change', function() {
                if (!this.files || !this.files[0]) return;
                const slot = this.dataset.slot;
                const formData = new FormData();
                formData.append('logo', this.files[0]);
                formData.append('slot', slot);
                const img = this.closest('.logo-editable').querySelector('img');
                const overlay = this.closest('.logo-editable').querySelector('.logo-overlay');
                if (overlay) overlay.textContent = '⏳ Subiendo...';
                fetch('subir_logo.php', { method: 'POST', body: formData })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.success) {
                            if (img) { img.src = data.url; } else { window.location.reload(); }
                        } else { alert('No se pudo cambiar el logo: ' + data.mensaje); }
                        if (overlay) overlay.textContent = '📷 Cambiar';
                    })
                    .catch(function() {
                        alert('Error de conexión al subir el logo.');
                        if (overlay) overlay.textContent = '📷 Cambiar';
                    });
            });
        });
    </script>
</body>
</html>
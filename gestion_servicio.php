<?php
session_start();
include 'config.php';

// =========================================================
// LÓGICA DEL ENCABEZADO (COPIADA DE INDEX.PHP)
// =========================================================
$logueado = isset($_SESSION['usuario_id']);

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

// ===== OBTENER EL PERIODO DE LA BASE DE DATOS =====
$periodo = '2024-2030';
$sql_textos = "SELECT valor FROM configuracion WHERE clave = 'periodo'";
$result_textos = $conn->query($sql_textos);
if ($result_textos && $result_textos->num_rows > 0) {
    $row = $result_textos->fetch_assoc();
    $periodo = $row['valor'];
}
// =========================================================

// --- PROCESAR RESPALDO ---
if (isset($_GET['accion']) && $_GET['accion'] == 'respaldar') {
    $sql_respaldar = "UPDATE servicios 
                      SET anio_archivado = YEAR(fecha_registro) 
                      WHERE anio_archivado IS NULL OR anio_archivado = 0";
    $conn->query($sql_respaldar);
    
    header('Location: gestion_servicio.php');
    exit;
}

// --- PROCESAR CAMBIAR ESTADO ---
if (isset($_GET['accion']) && isset($_GET['id']) && $_GET['accion'] != 'respaldar') {
    $id = intval($_GET['id']);
    $nuevo_estado = ($_GET['accion'] == 'resolver') ? 1 : 0;
    $sql_update = "UPDATE servicios SET resuelto = $nuevo_estado WHERE id = $id";
    $conn->query($sql_update);
    header('Location: gestion_servicio.php');
    exit;
}

// --- OBTENER CONTADORES (NULL O 0) ---
$sql_total = "SELECT COUNT(*) as total FROM servicios WHERE anio_archivado IS NULL OR anio_archivado = 0";
$result_total = $conn->query($sql_total);
$total_servicios = $result_total->fetch_assoc()['total'];

$sql_pendientes = "SELECT COUNT(*) as pendientes FROM servicios WHERE resuelto = 0 AND (anio_archivado IS NULL OR anio_archivado = 0)";
$result_pendientes = $conn->query($sql_pendientes);
$total_pendientes = $result_pendientes->fetch_assoc()['pendientes'];

$sql_resueltos = "SELECT COUNT(*) as resueltos FROM servicios WHERE resuelto = 1 AND (anio_archivado IS NULL OR anio_archivado = 0)";
$result_resueltos = $conn->query($sql_resueltos);
$total_resueltos = $result_resueltos->fetch_assoc()['resueltos'];

// --- OBTENER SERVICIOS ACTIVOS (NULL O 0) ---
$sql = "SELECT * FROM servicios WHERE anio_archivado IS NULL OR anio_archivado = 0 ORDER BY id DESC";
$result = $conn->query($sql);

// --- OBTENER AÑOS ÚNICOS PARA EL HISTÓRICO ---
$sql_anios = "SELECT DISTINCT anio_archivado FROM servicios WHERE anio_archivado IS NOT NULL AND anio_archivado > 0 ORDER BY anio_archivado DESC";
$result_anios = $conn->query($sql_anios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; min-height:100vh; padding:20px; }
        .container { max-width:1200px; margin:0 auto; background:#fff; border-radius:12px; padding:25px 30px; box-shadow:0 8px 30px rgba(0,0,0,0.1); }

        /* ========================================
           NUEVO ENCABEZADO SUPERIOR (LOGO IZQUIERDA + RELOJ DERECHA)
           ======================================== */
        .header-superior {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a3a5c;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-superior .logo-izquierda {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-superior .logo-izquierda .textos-logo {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .header-superior .logo-izquierda .textos-logo .humanismo {
            font-size: 14px;
            font-weight: 700;
            color: #1a3a5c;
            letter-spacing: 1px;
        }

        .header-superior .logo-izquierda .textos-logo .secret {
            font-size: 16px;
            font-weight: 800;
            color: #b8860b;
            letter-spacing: 0.5px;
        }

        .header-superior .logo-izquierda .textos-logo .gobierno {
            font-size: 14px;
            font-weight: 600;
            color: #1a3a5c;
        }

        .header-superior .logo-izquierda .textos-logo .periodo {
            font-size: 13px;
            font-weight: 600;
            color: #1a3a5c;
        }

        /* ========================================
           RELOJ EN LA PARTE SUPERIOR DERECHA
           ======================================== */
        .reloj {
            font-size: 18px;
            font-weight: 700;
            color: #1a3a5c;
            background: #f5f7fa;
            padding: 8px 20px;
            border-radius: 8px;
            border: 2px solid #b8860b;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }

        .reloj .fecha {
            font-size: 14px;
            font-weight: 600;
            color: #6c757d;
            display: block;
            text-align: center;
        }

        .reloj .hora {
            display: block;
            text-align: center;
            font-size: 22px;
        }

        /* ========================================
           TÍTULO CENTRADO (Estilo igual a index.php)
           ======================================== */
        .header-titulo {
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 20px;
            margin-top: 5px;
        }

        .titulo-gestion {
            font-size: 28px;
            font-weight: 700;
            color: #1a3a5c;
            display: inline-block;
            padding: 10px 30px;
            background: #f5f7fa;
            border-radius: 8px;
            border-left: 4px solid #b8860b;
            border-right: 4px solid #b8860b;
        }

        /* ========================================
           LOGOS EDITABLES (clic para cambiar imagen)
           ======================================== */
        .logo-editable {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 8px;
            overflow: hidden;
        }
        .logo-editable img {
            display: block;
        }
        .logo-editable .logo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(26, 58, 92, 0.55);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            opacity: 0;
            transition: opacity 0.2s;
            padding: 4px;
        }
        .logo-editable:hover .logo-overlay {
            opacity: 1;
        }
        .logo-editable input[type="file"] {
            display: none;
        }

        /* ========================================
           ESTILOS DE GESTIÓN DE SERVICIOS
           ======================================== */
        .cards-container { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 30px; }
        .card { flex: 1; background: #fff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .card h2 { font-size: 36px; margin-bottom: 5px; }
        .card p { color: #666; font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 5px; }
        .card-total h2 { color: #1a3a5c; }
        .card-pendientes h2 { color: #dc3545; }
        .card-resueltos h2 { color: #28a745; }

        .header-botones { 
            display: flex; 
            justify-content: flex-end; 
            gap: 10px; 
            flex-wrap: wrap; 
            margin-bottom: 25px; 
        }
        .btn { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 8px; 
            font-weight: 600; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
            transition: all 0.3s; 
            font-size: 14px; 
        }
        .btn-success { background:#28a745; color:#fff; }
        .btn-success:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(40,167,69,0.3); }
        .btn-pendiente { background:#ffc107; color:#fff; }
        .btn-pendiente:hover { background:#e0a800; }
        .btn-resuelto { background:#28a745; color:#fff; }
        .btn-resuelto:hover { background:#218838; }
        .btn-danger { background:#dc3545; color:#fff; }
        .btn-danger:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(220,53,69,0.3); }
        .btn-volver { background:#6c757d; color:#fff; }
        .btn-historico { background:#6f42c1; color:#fff; }
        .btn-warning { background:#ffc107; color:#fff; }
        .btn-respaldo { background: #17a2b8; color: #fff; }
        .btn-respaldo:hover { background: #138496; transform: translateY(-2px); }
        .btn-inventario { background: #20c997; color: #fff; }
        .btn-inventario:hover { background: #1aa179; transform: translateY(-2px); }

        .table-responsive { overflow-x:auto; margin-top:15px; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        thead { background:#1a3a5c; color:#fff; }
        th { padding:12px 15px; text-align:left; white-space:nowrap; }
        td { padding:10px 15px; border-bottom:1px solid #e6eaef; vertical-align:middle; }
        tbody tr:hover { background:#f5f8fc; }
        .acciones { display:flex; gap:5px; flex-wrap:wrap; }
        .acciones .btn { padding:5px 12px; font-size:12px; }
        .badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-pendiente { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
        .badge-resuelto { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }

        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center; }
        .modal.active { display:flex; }
        .modal-content { background:#fff; border-radius:16px; padding:30px 35px; max-width:500px; width:90%; max-height:80vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .modal-content h2 { color:#1a3a5c; margin-bottom:20px; border-bottom:2px solid #eee; padding-bottom:10px; }
        .modal-content .year-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:15px; }
        .modal-content .year-grid .btn { padding:15px; text-align:center; font-size:18px; font-weight:700; background:#e9ecef; color:#1a3a5c; border-radius:10px; }
        .modal-content .year-grid .btn:hover { background:#1a3a5c; color:#fff; transform:scale(1.05); }
        .modal-close { position:absolute; top:15px; right:20px; font-size:28px; cursor:pointer; color:#999; }
        .modal-close:hover { color:#333; }
        .modal-content { position:relative; }

        @media (max-width:768px) {
            .header-superior { flex-direction: column; align-items: center; text-align: center; }
            .header-superior .logo-izquierda { flex-direction: column; text-align: center; }
            .reloj { width: 100%; text-align: center; }
            .titulo-gestion { font-size: 22px; padding: 8px 20px; }
            .header-botones { justify-content: center; flex-direction: column; width: 100%; }
            .header-botones .btn { width: 100%; text-align: center; }
            .cards-container { flex-direction: column; }
            .container { padding:15px; }
        }
    </style>
</head>
<body>
<div class="container">
    
    <!-- ========================================
         HEADER SUPERIOR (LOGO IZQUIERDA + RELOJ DERECHA)
         ======================================== -->
    <div class="header-superior">
        <div class="logo-izquierda">
            <!-- LOGO IZQUIERDO -->
            <?php if ($logueado): ?>
                <label class="logo-editable" title="Haz clic para cambiar el logo">
                    <img src="<?php echo htmlspecialchars($logo_izquierdo); ?>" alt="Logotipo Secretaría de Finanzas" style="height:70px; width:auto;">
                    <span class="logo-overlay">📷 Cambiar</span>
                    <input type="file" accept="image/png,image/jpeg,image/webp" data-slot="izquierdo" class="input-logo">
                </label>
            <?php else: ?>
                <img src="<?php echo htmlspecialchars($logo_izquierdo); ?>" alt="Logotipo Secretaría de Finanzas" style="height:70px; width:auto;">
            <?php endif; ?>

            <!-- TEXTOS DE LA DEPENDENCIA -->
            <div class="textos-logo">
                <span class="humanismo">HUMANISMO QUE TRANSFORMA</span>
                <span class="secret">SECRETARÍA DE FINANZAS</span>
                <span class="gobierno">GOBIERNO DE CHIAPAS</span>
                <span class="periodo"><?php echo $periodo; ?></span>
            </div>

            <!-- LOGO DERECHO -->
            <?php if ($logueado && $logo_derecho): ?>
                <img src="<?php echo htmlspecialchars($logo_derecho); ?>" alt="Segundo logotipo" style="height:70px; width:auto;">
            <?php elseif (!$logueado && $logo_derecho): ?>
                <img src="<?php echo htmlspecialchars($logo_derecho); ?>" alt="Segundo logotipo" style="height:70px; width:auto;">
            <?php endif; ?>
        </div>
        
        <!-- RELOJ EN LA PARTE SUPERIOR DERECHA -->
        <div class="reloj" id="reloj">
            <span class="fecha" id="fecha">Cargando fecha...</span>
            <span class="hora" id="hora">Cargando hora...</span>
        </div>
    </div>

    <!-- TÍTULO CENTRADO -->
    <div class="header-titulo">
        <span class="titulo-gestion">📋 Gestión de Servicios</span>
    </div>

    <!-- BOTONES DE ACCIÓN -->
    <div class="header-botones">
        <a href="gestion_servicio.php?accion=respaldar" class="btn btn-respaldo" onclick="return confirm('¿Estás seguro de que quieres respaldar TODOS los servicios activos?')">💾 Respaldar</a>
        <a href="nuevo_servicio.php" class="btn btn-success">➕ Nuevo Servicio</a>
        <a href="inventario.php" class="btn btn-inventario">📦 Inventario</a>
        <button onclick="abrirHistorico()" class="btn btn-historico">📜 Histórico</button>
        <a href="menu.php" class="btn btn-volver">⬅ Volver</a>
    </div>

    <!-- TARJETAS DE CONTADORES -->
    <div class="cards-container">
        <div class="card card-total"><h2><?php echo $total_servicios; ?></h2><p>📊 Total Servicios</p></div>
        <div class="card card-pendientes"><h2><?php echo $total_pendientes; ?></h2><p>⏳ Pendientes</p></div>
        <div class="card card-resueltos"><h2><?php echo $total_resueltos; ?></h2><p>✅ Resueltos</p></div>
    </div>

    <!-- TABLA DE SERVICIOS -->
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_solicitante'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion_equipo'] ?? ''); ?></td>
                            <td>
                                <?php 
                                    $resuelto = isset($row['resuelto']) ? $row['resuelto'] : 0;
                                    if($resuelto == 1) { echo '<span class="badge badge-resuelto">✅ Resuelto</span>'; } 
                                    else { echo '<span class="badge badge-pendiente">⏳ Pendiente</span>'; }
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'] ?? 'now')); ?></td>
                            <td>
                                <div class="acciones">
                                    <a href="editar_servicio.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">✏️ Editar</a>
                                    <?php if(isset($row['resuelto']) && $row['resuelto'] == 1): ?>
                                        <a href="gestion_servicio.php?accion=pendiente&id=<?php echo $row['id']; ?>" class="btn btn-pendiente" onclick="return confirm('¿Marcar como pendiente?')">⏳ Pendiente</a>
                                    <?php else: ?>
                                        <a href="gestion_servicio.php?accion=resolver&id=<?php echo $row['id']; ?>" class="btn btn-resuelto" onclick="return confirm('¿Marcar como resuelto?')">✅ Resolver</a>
                                    <?php endif; ?>
                                    <a href="eliminar.php?tabla=servicios&id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar este servicio?')">🗑️ Eliminar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:#999;">
                            📭 No hay servicios activos en este momento.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL DE HISTÓRICO -->
<div id="modalHistorico" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="cerrarHistorico()">&times;</span>
        <h2>📜 Histórico de Servicios</h2>
        <p style="color:#6c757d; margin-bottom:10px;">Selecciona un año para ver los servicios de ese período:</p>
        <div class="year-grid">
            <?php if ($result_anios->num_rows > 0): ?>
                <?php while($row_anio = $result_anios->fetch_assoc()): ?>
                    <a href="historico_servicios.php?year=<?php echo $row_anio['anio_archivado']; ?>" class="btn"><?php echo $row_anio['anio_archivado']; ?></a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: span 3; text-align:center; color:#999;">No hay registros en el histórico.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function abrirHistorico() { document.getElementById('modalHistorico').classList.add('active'); }
    function cerrarHistorico() { document.getElementById('modalHistorico').classList.remove('active'); }
    window.onclick = function(event) {
        const modalHist = document.getElementById('modalHistorico');
        if (event.target == modalHist) { modalHist.classList.remove('active'); }
    }

    // ===== RELOJ EN TIEMPO REAL =====
    function actualizarReloj() {
        const ahora = new Date();
        const opcionesFecha = { year: 'numeric', month: '2-digit', day: '2-digit' };
        const fechaFormateada = ahora.toLocaleDateString('es-MX', opcionesFecha);
        const horas = String(ahora.getHours()).padStart(2, '0');
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        const segundos = String(ahora.getSeconds()).padStart(2, '0');
        const horaFormateada = horas + ':' + minutos + ':' + segundos;
        document.getElementById('fecha').textContent = '📅 ' + fechaFormateada;
        document.getElementById('hora').textContent = '🕐 ' + horaFormateada;
    }
    actualizarReloj();
    setInterval(actualizarReloj, 1000);

    // ===== SUBIR LOGO =====
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
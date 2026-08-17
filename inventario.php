<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// 👇 CAMBIO 1: FILTRAR SOLO EQUIPOS (es_equipo = 1)
$sql = "SELECT * FROM servicios WHERE es_equipo = 1 ORDER BY fecha_registro DESC";

if (!empty($busqueda)) {
    $busqueda_segura = mysqli_real_escape_string($conn, $busqueda);
    // 👇 CAMBIO 2: AGREGAR EL FILTRO DE EQUIPO TAMBIÉN EN LA BÚSQUEDA
    $sql = "SELECT * FROM servicios 
            WHERE es_equipo = 1
              AND (numero_inventario LIKE '%$busqueda_segura%' 
               OR nombre_solicitante LIKE '%$busqueda_segura%' 
               OR descripcion_equipo LIKE '%$busqueda_segura%') 
            ORDER BY fecha_registro DESC";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario General de Servicios</title>
    <!-- 📷 LIBRERÍA PARA EL ESCÁNER QR -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; min-height:100vh; padding:20px; }
        .container { max-width:1200px; margin:0 auto; background:#fff; border-radius:12px; padding:25px 30px; box-shadow:0 8px 30px rgba(0,0,0,0.1); }
        
        .header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:25px; border-bottom:3px solid #1a3a5c; padding-bottom:15px; }
        .header h1 { color:#1a3a5c; font-size:28px; }
        
        .btn { padding:10px 20px; border:none; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; transition:all 0.3s; font-size:14px; }
        .btn-inventario { background: #20c997; color: #fff; }
        .btn-inventario:hover { background: #1aa179; transform: translateY(-2px); }
        .btn-volver { background:#6c757d; color:#fff; }
        .btn-volver:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(108,117,125,0.3); }
        .btn-imprimir { background:#17a2b8; color:#fff; }
        .btn-imprimir:hover { background:#138496; transform: translateY(-2px); }

        /* BOTÓN DE AGREGAR EQUIPO */
        .btn-agregar-equipo { background: #28a745; color: #fff; }
        .btn-agregar-equipo:hover { background: #218838; transform: translateY(-2px); }

        /* ESTILOS DEL BUSCADOR Y ESCÁNER */
        .buscador-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            flex-wrap: wrap;
        }
        .buscador-container input {
            flex: 1;
            min-width: 200px;
            max-width: 500px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: border 0.3s;
        }
        .buscador-container input:focus { border-color: #1a3a5c; }
        .btn-buscar { background: #1a3a5c; color: #fff; }
        .btn-buscar:hover { background: #2c5f7c; }
        .btn-limpiar { background: #6c757d; color: #fff; }
        
        .btn-escanear { background: #28a745; color: #fff; display: flex; align-items: center; gap: 5px; }
        .btn-escanear:hover { background: #218838; }

        .table-responsive { overflow-x:auto; margin-top:15px; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        thead { background:#1a3a5c; color:#fff; }
        th { padding:12px 15px; text-align:left; white-space:nowrap; }
        td { padding:10px 15px; border-bottom:1px solid #e6eaef; vertical-align:middle; }
        tbody tr:hover { background:#f5f8fc; }
        
        .badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-pendiente { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
        .badge-resuelto { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .badge-activo { background:#d1ecf1; color:#0c5460; border:1px solid #bee5eb; }
        .badge-archivado { background:#e2e3e5; color:#383d41; border:1px solid #d6d8db; }
        .etiqueta-inventario {
            font-family: monospace; background: #f1f2f6; padding: 2px 8px; border-radius: 4px; border: 1px dashed #8395a7; font-size: 13px; color: #2f3542; letter-spacing: 0.5px;
        }

        /* ESTILO PARA EL BOTÓN DE EDITAR EN LA TABLA */
        .btn-editar-tabla {
            background: #ffc107;
            color: #fff;
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-editar-tabla:hover { background: #e0a800; transform: scale(1.05); }

        /* Modal para mostrar el lector QR */
        #modalLector { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center; flex-direction: column; }
        #modalLector.active { display: flex; }
        #modalLector .contenedor { background: #fff; padding: 20px; border-radius: 16px; width: 90%; max-width: 400px; text-align: center; position: relative; }
        #modalLector .cerrar { position: absolute; top: 10px; right: 20px; font-size: 30px; cursor: pointer; color: #999; }
        #modalLector .cerrar:hover { color: #333; }
        #reader { width: 100%; max-width: 350px; margin: 0 auto; }

        @media (max-width:768px) {
            .header { flex-direction:column; align-items:stretch; }
            .header .btn { width:100%; text-align:center; }
            .buscador-container { flex-direction: column; align-items: center; }
            .buscador-container input, .buscador-container .btn { width: 100%; max-width: 100%; text-align: center; }
            #modalLector .contenedor { width: 95%; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📦 Inventario </h1>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- 🖨️ BOTÓN DE IMPRIMIR -->
            <a href="imprimir_inventario.php" target="_blank" class="btn btn-imprimir">🖨️ Imprimir</a>

            <!-- ➕ BOTÓN PARA AGREGAR EQUIPO -->
            <a href="nuevo_equipo.php" class="btn btn-agregar-equipo">➕ Agregar Equipo</a>

            <a href="historico_servicios.php" class="btn btn-inventario">📜 Ir a Histórico</a>
            <a href="gestion_servicio.php" class="btn btn-volver">⬅ Volver</a>
        </div>
    </div>

    <!-- BUSCADOR Y ESCÁNER QR -->
    <form method="GET" action="inventario.php" class="buscador-container">
        <input type="text" name="buscar" placeholder="🔍 Buscar por número de inventario, nombre o equipo..." value="<?php echo htmlspecialchars($busqueda); ?>">
        <button type="submit" class="btn btn-buscar">🔍 Buscar</button>
        
        <!-- 📷 BOTÓN PARA ACTIVAR EL ESCÁNER QR -->
        <button type="button" id="btn-escanear" class="btn btn-escanear">📷 Escanear</button>
        
        <?php if (!empty($busqueda)): ?>
            <a href="inventario.php" class="btn btn-limpiar">✖ Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>N° Inventario</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Estado General</th>
                    <th>Fecha Registro</th>
                    <th>Histórico</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php 
                    $contador = 1; 
                    while($row = $result->fetch_assoc()): 
                    ?>
                        <tr>
                            <td><?php echo $contador; $contador++; ?></td>
                            <td>
                                <?php 
                                    if (!empty($row['numero_inventario'])) {
                                        echo '<span class="etiqueta-inventario">' . htmlspecialchars($row['numero_inventario']) . '</span>';
                                    } else {
                                        echo '<span style="color:#999; font-size:12px;">Sin etiqueta</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['nombre_solicitante'] ?? 'Sin nombre'); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion_equipo'] ?? 'Sin equipo'); ?></td>
                            <td>
                                <?php 
                                    $estado_activo = isset($row['anio_archivado']) && ($row['anio_archivado'] === NULL || $row['anio_archivado'] == 0);
                                    if($estado_activo) {
                                        echo '<span class="badge badge-activo">🟢 Activo</span>';
                                    } else {
                                        echo '<span class="badge badge-archivado">📦 Archivado ' . $row['anio_archivado'] . '</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'] ?? 'now')); ?></td>
                            <td><?php echo ($row['anio_archivado'] > 0) ? '✅ Sí' : '❌ No'; ?></td>
                            
                            <!-- ✏️ BOTÓN DE EDITAR -->
                            <td>
                               <a href="editar_equipo.php?id=<?php echo $row['id']; ?>" class="btn-editar-tabla">✏️ Editar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center; padding:40px; color:#999;">
                            📭 No se encontraron equipos registrados con esa búsqueda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 📷 MODAL PARA EL LECTOR DE QR -->
<div id="modalLector">
    <div class="contenedor">
        <span class="cerrar" onclick="cerrarLectorQR()">&times;</span>
        <h3 style="margin-bottom: 15px; color: #1a3a5c;">📷 Escanea el código QR</h3>
        <p style="font-size: 14px; color: #666; margin-bottom: 15px;">Apunta la cámara al código QR de la etiqueta.</p>
        <div id="reader"></div>
    </div>
</div>

<script>
    // 1. ABRIR EL LECTOR QR
    document.getElementById('btn-escanear').addEventListener('click', function() {
        document.getElementById('modalLector').classList.add('active');
        
        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" }, // Usa la cámara trasera
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess
        );

        function onScanSuccess(decodedText, decodedResult) {
            // Escribe el código en el buscador y envía el formulario
            document.querySelector('input[name="buscar"]').value = decodedText;
            document.querySelector('form').submit();
            html5QrCode.stop();
            document.getElementById('modalLector').classList.remove('active');
        }
    });

    // 2. CERRAR EL LECTOR QR
    function cerrarLectorQR() {
        document.getElementById('modalLector').classList.remove('active');
        // Detener la cámara si está corriendo
        const readerElement = document.getElementById('reader');
        if(readerElement.innerHTML !== '') {
            readerElement.innerHTML = ''; // Limpia el lector al cerrar
        }
    }

    // Cerrar modal si se hace clic fuera del cuadro blanco
    document.getElementById('modalLector').addEventListener('click', function(event) {
        if (event.target === this) {
            cerrarLectorQR();
        }
    });
</script>
</body>
</html>
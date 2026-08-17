<?php
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0) {
    header('Location: historico_servicios.php?error=ID no válido');
    exit;
}

$sql = "SELECT * FROM servicios WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    header('Location: historico_servicios.php?error=Servicio no encontrado');
    exit;
}
$servicio = $result->fetch_assoc();

// 👇 NUEVA LÓGICA PARA EL FOLIO BONITO
$folio_real = $servicio['folio']; // Respaldo del original
$anio_servicio = date('Y', strtotime($servicio['fecha_registro']));
$num_folio = $servicio['folio_anual'];
// Si tiene folio_anual, mostramos el formato bonito. Si no, mostramos el original.
$folio_mostrar = (!empty($num_folio)) ? $anio_servicio . '-' . str_pad($num_folio, 4, '0', STR_PAD_LEFT) : $folio_real;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Histórico - Folio <?php echo $folio_mostrar; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
        .header-detalle { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border-bottom: 3px solid #6f42c1; padding-bottom: 15px; margin-bottom: 25px; }
        .header-detalle h1 { color: #6f42c1; font-size: 24px; }
        .header-detalle .folio-badge { background: #6f42c1; color: #fff; padding: 8px 25px; border-radius: 20px; font-weight: 700; font-size: 18px; }
        .btn-volver { background: #6c757d; color: #fff; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .btn-volver:hover { background: #5a6268; transform: translateY(-2px); }
        .seccion-detalle { background: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 20px; border-left: 4px solid #6f42c1; }
        .seccion-detalle h3 { color: #6f42c1; font-size: 16px; margin-bottom: 15px; border-bottom: 1px solid #e6eaef; padding-bottom: 10px; }
        .grid-datos { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 25px; }
        .grid-datos .dato { display: flex; flex-direction: column; }
        .grid-datos .dato .etiqueta { font-size: 12px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .grid-datos .dato .valor { font-size: 15px; font-weight: 600; color: #1e2a3a; margin-top: 2px; }
        .grid-datos .dato-full { grid-column: 1 / -1; }
        .grid-datos .dato-full .valor { background: #fff; padding: 10px; border-radius: 6px; border: 1px solid #e6eaef; font-weight: 400; }
        .badge-estado { display: inline-block; padding: 4px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .badge-resuelto { background: #d4edda; color: #155724; }
        .badge-pendiente { background: #f8d7da; color: #721c24; }
        .badge-definitiva { background: #d1ecf1; color: #0c5460; }
        .badge-temporal { background: #fff3cd; color: #856404; }
        .footer-detalle { margin-top: 30px; padding-top: 20px; border-top: 2px solid #e6eaef; text-align: center; color: #999; font-size: 13px; }
        .acciones-botones { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
        @media (max-width: 768px) { .container { padding: 15px; } .header-detalle { flex-direction: column; text-align: center; } .header-detalle h1 { font-size: 20px; } .grid-datos { grid-template-columns: 1fr; } .acciones-botones { flex-direction: column; width: 100%; } .acciones-botones .btn { width: 100%; text-align: center; } }
        @media print { body { background: white; padding: 10px; } .container { box-shadow: none; border: 1px solid #ddd; padding: 20px; } .no-print { display: none !important; } .seccion-detalle { background: #f8f9fa !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; } .badge-estado { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-detalle">
            <div><h1>📋 Detalle del Histórico</h1></div>
            <div class="folio-badge">Folio: <?php echo $folio_mostrar; ?></div>
        </div>

        <!-- DATOS GENERALES -->
        <div class="seccion-detalle">
            <h3>📋 Datos Generales</h3>
            <div class="grid-datos">
                <div class="dato"><span class="etiqueta">ID</span><span class="valor">#<?php echo $servicio['id']; ?></span></div>
                <div class="dato"><span class="etiqueta">Fecha de solicitud</span><span class="valor"><?php echo date('d/m/Y', strtotime($servicio['fecha_solicitud'])); ?></span></div>
                <div class="dato dato-full"><span class="etiqueta">Nombre del solicitante</span><span class="valor"><?php echo htmlspecialchars($servicio['nombre_solicitante']); ?></span></div>
                <div class="dato"><span class="etiqueta">Subsecretaría</span><span class="valor"><?php echo htmlspecialchars($servicio['subsecretaria']); ?></span></div>
                <div class="dato"><span class="etiqueta">Dirección</span><span class="valor"><?php echo htmlspecialchars($servicio['direccion']); ?></span></div>
                <div class="dato dato-full"><span class="etiqueta">Departamento/Área</span><span class="valor"><?php echo htmlspecialchars($servicio['departamento']); ?></span></div>
            </div>
        </div>

        <!-- DATOS DEL EQUIPO -->
        <div class="seccion-detalle">
            <h3>💻 Datos del Equipo</h3>
            <div class="grid-datos">
                <div class="dato"><span class="etiqueta">Descripción</span><span class="valor"><?php echo htmlspecialchars($servicio['descripcion_equipo']); ?></span></div>
                <div class="dato"><span class="etiqueta">Marca</span><span class="valor"><?php echo htmlspecialchars($servicio['marca']); ?></span></div>
                <div class="dato"><span class="etiqueta">Modelo</span><span class="valor"><?php echo htmlspecialchars($servicio['modelo']); ?></span></div>
                <div class="dato"><span class="etiqueta">Serie</span><span class="valor"><?php echo htmlspecialchars($servicio['serie']); ?></span></div>
                <div class="dato dato-full"><span class="etiqueta">Falla reportada</span><span class="valor"><?php echo nl2br(htmlspecialchars($servicio['falla_reportada'])); ?></span></div>
            </div>
        </div>

        <!-- DATOS DE SOPORTE -->
        <div class="seccion-detalle">
            <h3>🔧 Datos de Soporte</h3>
            <div class="grid-datos">
                <div class="dato"><span class="etiqueta">Nombre de quién entrega</span><span class="valor"><?php echo htmlspecialchars($servicio['nombre_entrega']); ?></span></div>
                <div class="dato"><span class="etiqueta">Soporte quién recibe</span><span class="valor"><?php echo htmlspecialchars($servicio['soporte_recibe']); ?></span></div>
                <div class="dato dato-full"><span class="etiqueta">Acción realizada</span><span class="valor"><?php echo nl2br(htmlspecialchars($servicio['accion_realizada'])); ?></span></div>
                <div class="dato"><span class="etiqueta">Estatus</span><span class="valor"><span class="badge-estado <?php echo ($servicio['resuelto'] == 1) ? 'badge-resuelto' : 'badge-pendiente'; ?>"><?php echo ($servicio['resuelto'] == 1) ? 'Resuelto' : 'Pendiente'; ?></span></span></div>
                <div class="dato"><span class="etiqueta">Modalidad</span><span class="valor"><span class="badge-estado <?php echo $servicio['modalidad'] == 'Definitiva' ? 'badge-definitiva' : 'badge-temporal'; ?>"><?php echo $servicio['modalidad']; ?></span></span></div>
            </div>
        </div>

        <!-- SITUACIÓN FINAL -->
        <div class="seccion-detalle">
            <h3>📌 Situación Final del Servicio</h3>
            <div class="grid-datos">
                <div class="dato"><span class="etiqueta">Fecha de realización</span><span class="valor"><?php echo date('d/m/Y', strtotime($servicio['fecha_realizacion'])); ?></span></div>
                <div class="dato"><span class="etiqueta">Hora de conclusión</span><span class="valor"><?php echo $servicio['hora_conclusion']; ?></span></div>
                <div class="dato"><span class="etiqueta">Nombre de quién recibe</span><span class="valor"><?php echo htmlspecialchars($servicio['nombre_recibe']); ?></span></div>
                <div class="dato"><span class="etiqueta">Soporte quién entrega</span><span class="valor"><?php echo htmlspecialchars($servicio['soporte_entrega']); ?></span></div>
            </div>
        </div>

        <div class="acciones-botones no-print">
            <a href="historico_servicios.php?year=<?php echo $servicio['anio_archivado']; ?>" class="btn btn-volver" style="background:#6c757d; color:#fff; padding:10px 25px; border-radius:8px; text-decoration:none; font-weight:600;">⬅ Volver al Histórico</a>
        </div>

        <div class="footer-detalle">
            <p>Registro archivado en el año <?php echo $servicio['anio_archivado']; ?> - Sistema de Gestión de Servicios</p>
            <p style="margin-top: 5px; font-size: 12px;">Secretaría de Finanzas de Chiapas - 2024-2030</p>
        </div>

    </div>
</body>
</html>